defmodule PhoenixApi.RateLimiterTest do
  use ExUnit.Case, async: true

  alias PhoenixApi.RateLimiter

  setup do
    name = :"rate_limiter_#{:erlang.unique_integer([:positive])}"
    table_name = :"rate_table_#{:erlang.unique_integer([:positive])}"
    {:ok, _pid} = RateLimiter.start_link(name: name, table_name: table_name)
    {:ok, name: name}
  end

  test "allows requests under user limit", %{name: name} do
    for _ <- 1..5 do
      assert :ok == RateLimiter.check_rate(1, name)
    end
  end

  test "blocks user after exceeding per-user limit", %{name: name} do
    for _ <- 1..5 do
      assert :ok == RateLimiter.check_rate(1, name)
    end

    assert {:error, :user_rate_exceeded} == RateLimiter.check_rate(1, name)
  end

  test "different users have independent limits", %{name: name} do
    for _ <- 1..5 do
      assert :ok == RateLimiter.check_rate(1, name)
    end

    assert {:error, :user_rate_exceeded} == RateLimiter.check_rate(1, name)
    assert :ok == RateLimiter.check_rate(2, name)
  end

  test "first request is always allowed", %{name: name} do
    assert :ok == RateLimiter.check_rate(999, name)
  end
end
