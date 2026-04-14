# Notatki do zadania rekrutacyjnego

## Opis wprowadzonych zmian

### Architektura

Zdecydowałem się na **architekturę hexagonalną (ports & adapters)** z elementami **DDD**, ponieważ zadanie wprost mówi o projekcie rozwijanym przez lata przez kilku developerów. Struktura:

```
src/
├── Photo/           # Bounded context: zdjęcia
│   ├── Domain/      # Agregaty, Value Objects, eventy, wyjątki, interfejsy repo
│   ├── Application/ # Use Cases (Command/Query), DTOs, porty, event handlery
│   ├── Infrastructure/ # Doctrine, integracje zewnętrzne
│   └── UI/          # Actions z __invoke()
├── User/            # Bounded context: użytkownicy (analogiczna struktura)
└── Shared/          # Współdzielone kontrakty
    ├── Domain/      # AggregateRootInterface, ValueObjectInterface, enumy
    ├── Application/  # Bus interfaces, EventDispatcherInterface
    └── Infrastructure/ # Messenger buses, DomainEventDispatcher
```

Usunięty prefix `App\` z namespace'ów - `Photo\`, `User\`, `Shared\` bezpośrednio, bez zbędnego wrappera. `App\Kernel` pozostał dla kompatybilności z Symfony.

### Decyzje architektoniczne

**Command/Query Bus (CQRS-lite)** - trzy dedykowane busy Symfony Messenger:
- `command.bus` z middleware `doctrine_transaction` - dla operacji zmieniających stan
- `query.bus` - dla zapytań zwracających dane
- `event.bus` - dla asynchronicznych domain eventów (transport: doctrine)

Actions w UI dispatchują tylko wiadomości na bus - zero logiki biznesowej, jedna zależność (CommandBus lub QueryBus).

**EventDispatcherInterface** - interfejs w `Shared\Application`, implementacja (`DomainEventDispatcher`) w `Shared\Infrastructure`. Handlery w Application zależą od interfejsu, nie od implementacji - czysta separacja warstw.

**Interfejsy jako kontrakty** - każdy serwis, repozytorium i klient zewnętrzny ma interfejs w warstwie domeny/aplikacji. Implementacje w Infrastructure (Dependency Inversion Principle).

**AggregateRootInterface w Shared** - kontrakt bazowy z `pullDomainEvents()`. Abstrakcyjna klasa `AggregateRoot` dostarcza implementację z `recordEvent()`. Gotowe do użycia przez przyszłe agregaty.

**Read/Write Repository** - oddzielne interfejsy i implementacje. Read zwracają obiekty domenowe, Write translują z/do encji Doctrine.

**Encje Doctrine w Infrastructure** - encje ORM to szczegół implementacyjny (odzwierciedlenie tabel), nie model domenowy. Agregaty w Domain to czyste PHP z logiką biznesową, zero zależności od frameworka.

**Domain Events + Symfony Messenger** - `PhotoLiked`, `PhotoUnliked` emitowane przez agregat `Photo` i dispatchowane przez `EventDispatcherInterface`. `PhotosImported` dispatchowany bezpośrednio przez `ImportPhotosHandler` na event bus (nie jest eventem agregatowym). Przetwarzane asynchronicznie (transport: doctrine). Handlery invalidują cache Redis. W przyszłości łatwo dodać: notyfikacje, activity feed, statystyki.

**Generyczny mechanizm integracji** - `UserIntegration` z enumami `IntegrationProvider` i `IntegrationCredentialType`. Formularz w profilu z dropdownem providerów z enum. Dodanie nowego serwisu (Unsplash, Flickr) to: nowa wartość w enum, nowy klient w `Infrastructure/Integrations/`, nowy use case - Open/Closed Principle.

**Symfony Security** - custom `SessionAuthenticator` + `SecurityUserProvider` + `SecurityUser` wrapper. Domenowy User nie implementuje `UserInterface` - separacja frameworka od domeny. `#[IsGranted('ROLE_USER')]` na chronionych Actions, `access_control` w security.yaml. Logout przez natywny Symfony Security (`logout: path: /logout`).

**Value Objects** - `final readonly`, immutable, walidacja przy tworzeniu:
- Typed Identity: `PhotoId`, `UserId` (extends `AbstractId` - positive int, type safety zapobiega pomyłkom `findById($userId)`)
- Domain: `ImageUrl`, `Location`, `Camera`, `Username`, `Email`, `IntegrationCredentials`

**CSRF** - tokeny na wszystkich formularzach POST (like, import, save integration) z walidacją w Actions.

**PHP 8.5** - `readonly` klasy (DTOs, events, commands, handlers, repozytoria, serwisy), promoted properties.

**Cache (Redis)** - invalidacja przez domain event handlery przy like/unlike/import.

**CSS w osobnych plikach** - przeniesione z inline `<style>` w Twig do `public/css/` (app.css, home.css, profile.css).

### Naprawione błędy

1. **SQL Injection w AuthController** - bezpośrednia interpolacja `$token` i `$username` w SQL. Zastąpione przez Doctrine ORM z parametryzowanymi zapytaniami.
2. **Wyłączony firewall Symfony** (`security: false`) + hardcoded admin credentials - usunięte, zastąpione custom authenticatorem.
3. **Brak UNIQUE constraint na likes** - dodany `UNIQUE(user_id, photo_id)`.
4. **Stateful repository** (`LikeRepository::setUser()`) - antypattern usunięty.
5. **Problem N+1** w HomeController - zastąpiony pojedynczym query `findUserLikesForPhotos()`.
6. **Ręczne tworzenie repozytoriów** w kontrolerach - dependency injection.
7. **Like/unlike na GET** - zmienione na POST.
8. **Generyczne `\DomainException`** - zastąpione specyficznymi wyjątkami domenowymi (`PhotoNotFoundException`, `PhotoAlreadyLikedException`, `UserNotFoundException` itd.).

### Testy

Testy trzech rodzajów z **in-memory repositories** zamiast mocków na warstwie domenowej:

- **Unit** (czyste PHP):
  - Value Objects: walidacja, equality, edge cases
  - Agregaty: logika biznesowa, domain events, specyficzne wyjątki
  - Event handlery: mock cache
  - Use Case handlery: TogglePhotoLike, AuthenticateByToken, GetProfile
  - Bus implementations: CommandBus dispatch, QueryBus result extraction

- **Integration** (in-memory repos + stub klienty):
  - ImportPhotosHandler - flow importu, brak integracji, brak usera
  - SaveIntegrationHandler - zapis integracji, brak usera

- **Functional** (Symfony WebTestCase):
  - ListPhotosAction - smoke test, formularz filtrów, query params
  - ProfileAction - 401 bez sesji, logout redirect

- **Elixir**:
  - PhotoControllerTest - autentykacja, izolacja danych, filtrowanie pól
  - RateLimiterTest - limity per user, izolacja użytkowników, przepuszczanie do limitu

### Rate Limiting (Zadanie 4)

**OTP GenServer + ETS**:
- GenServer zarządza stanem i cyklem życia
- ETS zapewnia wysokowydajne lookups (read_concurrency)
- Per-user: 5 requestów / 10 minut
- Globalnie: 1000 requestów / godzinę
- Periodyczny cleanup co minutę
- Plug `RateLimit` po autentykacji
- W supervision tree - automatyczny restart przy awarii

### Napotkane problemy

**Property hooks (PHP 8.5) vs PHPStan** - początkowo użyłem property hooks w agregatach (`Photo`, `User`) do kontroli dostępu do kolekcji (`likedByUserIds`, `integrations`). PHPStan (nawet najnowsza wersja) nie obsługuje jeszcze tej składni na poziomie parsera - pliki z property hooks powodowały `Syntax error`. Świadoma decyzja: wróciłem do klasycznych getterów/setterów, żeby zachować pełną zgodność z PHPStan level 9. Property hooks to feature gotowy do użycia w runtime (PHP 8.5 działa poprawnie), ale tooling statycznej analizy jeszcze nie nadążył.

### Co zrobiłbym inaczej mając więcej czasu

- **Paginacja** galerii (cursor-based)
- **UUID zamiast auto-increment ID**
- **Event Sourcing** dla ścieżki audytowej
- **CI/CD pipeline** z PHPStan, CS-Fixer, testami
- **Observability** - structured logging, metryki, health checks
- **API dokumentacja** (OpenAPI)
- **Logowanie** - zmiana na bardziej bezpieczny mechanizm (np. OAuth2/JWT) zamiast tokenu w URL

### Sposób i stopień wykorzystania AI

Korzystałem z Claude Code jako narzędzia asystującego. AI pomagało w:
- Generowaniu boilerplate'u (encje, podstawowe struktury)
- Pisaniu testów
- Refaktoryzacji plików przy zmianach namespace'ów

Wszystkie decyzje architektoniczne, wybór wzorców i ocena trade-offów były moje. AI służyło jako przyśpieszenie implementacji, nie jako źródło decyzji projektowych.
