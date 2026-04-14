<?php

declare(strict_types=1);

namespace Tests\Unit\User\Application\UseCase;

use PHPUnit\Framework\TestCase;
use Tests\InMemory\InMemoryUserReadRepository;
use User\Application\UseCase\AuthenticateByToken\AuthenticateByTokenHandler;
use User\Application\UseCase\AuthenticateByToken\AuthenticateByTokenQuery;
use User\Domain\Aggregate\User;
use User\Domain\ValueObject\Email;
use User\Domain\ValueObject\UserId;
use User\Domain\ValueObject\Username;

class AuthenticateByTokenHandlerTest extends TestCase
{
    private InMemoryUserReadRepository $userReadRepo;
    private AuthenticateByTokenHandler $handler;

    protected function setUp(): void
    {
        $this->userReadRepo = new InMemoryUserReadRepository();
        $this->handler = new AuthenticateByTokenHandler($this->userReadRepo);
    }

    public function testReturnsUserDtoOnValidCredentials(): void
    {
        $user = new User(new UserId(1), new Username('nature_lover'), new Email('nature@example.com'), 'Emma', 'Wilson', 28, null);
        $this->userReadRepo->addUser($user);
        $this->userReadRepo->addAuthToken('valid-token-123', 1);

        $result = ($this->handler)(new AuthenticateByTokenQuery('nature_lover', 'valid-token-123'));

        $this->assertNotNull($result);
        $this->assertSame(1, $result->id);
        $this->assertSame('nature_lover', $result->username);
        $this->assertSame('nature@example.com', $result->email);
    }

    public function testReturnsNullOnInvalidToken(): void
    {
        $result = ($this->handler)(new AuthenticateByTokenQuery('anyone', 'bad-token'));

        $this->assertNull($result);
    }

    public function testReturnsNullWhenUsernameDoesNotMatchToken(): void
    {
        $user = new User(new UserId(1), new Username('nature_lover'), new Email('nature@example.com'), 'Emma', 'Wilson', 28, null);
        $this->userReadRepo->addUser($user);
        $this->userReadRepo->addAuthToken('valid-token-123', 1);

        $result = ($this->handler)(new AuthenticateByTokenQuery('wrong_user', 'valid-token-123'));

        $this->assertNull($result);
    }
}
