defmodule PhoenixApiWeb.Plugs.RateLimit do
  @moduledoc """
  Plug that enforces rate limiting on photo import requests.
  Must be used after Authenticate plug (requires conn.assigns.current_user).
  """
  import Plug.Conn
  import Phoenix.Controller

  def init(opts), do: opts

  def call(conn, _opts) do
    user = conn.assigns[:current_user]

    if user == nil do
      conn
    else
      case PhoenixApi.RateLimiter.check_rate(user.id) do
        :ok ->
          conn

        {:error, :user_rate_exceeded} ->
          conn
          |> put_status(:too_many_requests)
          |> json(%{errors: %{detail: "Rate limit exceeded. You can import photos up to 5 times per 10 minutes."}})
          |> halt()

        {:error, :global_rate_exceeded} ->
          conn
          |> put_status(:too_many_requests)
          |> json(%{errors: %{detail: "Service is currently busy. Please try again later."}})
          |> halt()
      end
    end
  end
end
