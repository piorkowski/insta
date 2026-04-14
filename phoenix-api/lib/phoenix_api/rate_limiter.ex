defmodule PhoenixApi.RateLimiter do
  @moduledoc """
  OTP GenServer-based rate limiter using ETS for high-performance lookups.

  Enforces two limits:
  - Per-user: max 5 requests per 10 minutes
  - Global: max 1000 requests per hour
  """
  use GenServer

  @user_limit 5
  @user_window_ms 10 * 60 * 1_000
  @global_limit 1_000
  @global_window_ms 60 * 60 * 1_000
  @cleanup_interval_ms 60 * 1_000

  # Client API

  def start_link(opts \\ []) do
    name = Keyword.get(opts, :name, __MODULE__)
    GenServer.start_link(__MODULE__, opts, name: name)
  end

  @doc """
  Check if a request is allowed for the given user_id.
  Returns :ok or {:error, :user_rate_exceeded} or {:error, :global_rate_exceeded}.
  """
  def check_rate(user_id, name \\ __MODULE__) do
    GenServer.call(name, {:check_rate, user_id})
  end

  # Server callbacks

  @impl true
  def init(opts) do
    table_name = Keyword.get(opts, :table_name, :rate_limiter)
    table = :ets.new(table_name, [:set, :public, read_concurrency: true])
    :ets.insert(table, {:global_requests, []})

    schedule_cleanup()

    {:ok, %{table: table}}
  end

  @impl true
  def handle_call({:check_rate, user_id}, _from, %{table: table} = state) do
    now = System.monotonic_time(:millisecond)

    result =
      with :ok <- check_global_limit(table, now),
           :ok <- check_user_limit(table, user_id, now) do
        record_request(table, user_id, now)
        :ok
      end

    {:reply, result, state}
  end

  @impl true
  def handle_info(:cleanup, %{table: table} = state) do
    now = System.monotonic_time(:millisecond)
    cleanup_expired(table, now)
    schedule_cleanup()
    {:noreply, state}
  end

  # Private

  defp check_global_limit(table, now) do
    global_requests =
      case :ets.lookup(table, :global_requests) do
        [{_, requests}] -> requests
        [] -> []
      end

    recent = Enum.filter(global_requests, fn ts -> now - ts < @global_window_ms end)

    if length(recent) >= @global_limit do
      {:error, :global_rate_exceeded}
    else
      :ok
    end
  end

  defp check_user_limit(table, user_id, now) do
    user_key = {:user, user_id}

    user_requests =
      case :ets.lookup(table, user_key) do
        [{_, requests}] -> requests
        [] -> []
      end

    recent = Enum.filter(user_requests, fn ts -> now - ts < @user_window_ms end)

    if length(recent) >= @user_limit do
      {:error, :user_rate_exceeded}
    else
      :ok
    end
  end

  defp record_request(table, user_id, now) do
    # Record user request
    user_key = {:user, user_id}

    user_requests =
      case :ets.lookup(table, user_key) do
        [{_, requests}] -> requests
        [] -> []
      end

    :ets.insert(table, {user_key, [now | user_requests]})

    # Record global request
    global_requests =
      case :ets.lookup(table, :global_requests) do
        [{_, requests}] -> requests
        [] -> []
      end

    :ets.insert(table, {:global_requests, [now | global_requests]})
  end

  defp cleanup_expired(table, now) do
    # Cleanup global
    case :ets.lookup(table, :global_requests) do
      [{_, requests}] ->
        recent = Enum.filter(requests, fn ts -> now - ts < @global_window_ms end)
        :ets.insert(table, {:global_requests, recent})

      [] ->
        :ok
    end

    # Cleanup user entries
    :ets.foldl(
      fn
        {{:user, _} = key, requests}, _acc ->
          recent = Enum.filter(requests, fn ts -> now - ts < @user_window_ms end)

          if recent == [] do
            :ets.delete(table, key)
          else
            :ets.insert(table, {key, recent})
          end

          :ok

        _, acc ->
          acc
      end,
      :ok,
      table
    )
  end

  defp schedule_cleanup do
    Process.send_after(self(), :cleanup, @cleanup_interval_ms)
  end
end
