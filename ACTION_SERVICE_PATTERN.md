# Action-Service Pattern (Laravel)

This guide explains the Action-Service pattern and gives a practical migration plan for your Laravel blog API repository. It shows why and when to use the pattern, how to structure code, concrete PHP/Laravel examples, testing guidance, and a step-by-step plan tailored for this repository.

---

## Summary — what the pattern is

-   Action-Service is a separation-of-concerns pattern that splits "what to do" (Action) from "how to do it" (Service).
-   Action: a small, single-purpose class that orchestrates a single application use-case (e.g., CreatePost). It accepts validated input (Request/DTO), calls one or more Services, dispatches events, and returns results.
-   Service: a reusable class that contains domain/persistence logic (e.g., `PostService` handles creating/updating posts, saving images, transactions).

Benefits:

-   Slim controllers — controllers delegate to Actions.
-   Testable units — Actions are easy to unit test; Services encapsulate business logic and are reusable.
-   Clear separation between orchestration and implementation.

When to use:

-   Medium/large apps where controller methods have grown complex.
-   When you want to reuse business logic across different entry points (CLI, jobs, controllers).

---

## Recommended repository structure

Suggested directories (Laravel app/ namespace):

-   `app/Actions/` — All Action classes, one per use-case. e.g., `Auth/RegisterUserAction.php`, `Post/CreatePostAction.php`.
-   `app/Services/` — Domain services. e.g., `PostService.php`, `ImageService.php`, `CommentService.php`.
-   `app/DTOs/` (optional) — Small data-transfer objects representing validated input.
-   `app/Repositories/` (optional) — Data access encapsulation (if you want a repository layer).
-   `app/Exceptions/` — Domain-specific exceptions used by Services/Actions.
-   `app/Contracts/` (optional) — Interfaces for services that might need swapping or mocking.

Keep existing API Resources in `app/Http/Resources/` and Requests in `app/Http/Requests/`.

Example placement:

```
app/
  Actions/
    Auth/
      RegisterUserAction.php
    Post/
      CreatePostAction.php
      UpdatePostAction.php
  Services/
    PostService.php
    ImageService.php
  DTOs/
    CreatePostData.php
```

---

## Patterns & conventions

-   Action naming: Verb + Subject + Action (e.g., `CreatePostAction`, `DeleteCommentAction`).
-   Single public method: `__invoke(CreatePostData $data)` or `handle(CreatePostData $data)`.
-   Actions are thin: validate/prepare data (often via FormRequest or DTO), call service(s), return domain model or Resource.
-   Services contain the heavy lifting; they may accept arrays or DTOs and return models.
-   Use constructor injection for dependencies (repositories, mailers, etc.).
-   Keep side-effects inside services (DB writes, file storage, external API calls); keep Actions orchestrating.

Return types:

-   Actions should return a domain value (Model, bool, array) or a standardized Result/Response object. Controllers return HTTP responses and Resources.

Transactions:

-   Wrap multi-step write operations in transactions inside Services (not controllers or Actions). Services can call `DB::transaction()` or accept a `DatabaseTransaction` dependency.

Error handling:

-   Services should throw domain-specific exceptions for validation/business rule failures. Actions catch them if they need to transform them to HTTP exceptions or let them bubble (Laravel exception handler maps them).

---

## Concrete PHP examples

Below are examples that match the existing structure of your repo.

1. DTO example (optional — simple immutable object for validated data)

```php
// app/DTOs/CreatePostData.php
namespace App\DTOs;

final class CreatePostData
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly ?string $imagePath,
        public readonly int $userId
    ) {}
}
```

2. Service example

```php
// app/Services/PostService.php
namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\DB;

class PostService
{
    public function create(array $data): Post
    {
        return DB::transaction(function () use ($data) {
            // handle image upload, slugs, any business rules
            return Post::create($data);
        });
    }

    public function update(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data) {
            $post->update($data);
            return $post->refresh();
        });
    }
}
```

3. Action example

```php
// app/Actions/Post/CreatePostAction.php
namespace App\Actions\Post;

use App\DTOs\CreatePostData;
use App\Services\PostService;
use App\Models\Post;

class CreatePostAction
{
    public function __construct(private PostService $postService) {}

    public function __invoke(CreatePostData $data): Post
    {
        // orchestrate: maybe call ImageService first, then PostService
        return $this->postService->create([
            'title' => $data->title,
            'content' => $data->content,
            'image' => $data->imagePath,
            'user_id' => $data->userId,
        ]);
    }
}
```

4. Controller usage

```php
// app/Http/Controllers/PostController.php
use App\Actions\Post\CreatePostAction;
use App\DTOs\CreatePostData;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;

public function store(StorePostRequest $request, CreatePostAction $action)
{
    $validated = $request->validated();
    $dto = new CreatePostData(
        $validated['title'],
        $validated['content'],
        $validated['image'] ?? null,
        $request->user()->id
    );

    $post = $action($dto);
    return new PostResource($post->load('user', 'comments'));
}
```

Notes:

-   Laravel's container will auto-resolve the `CreatePostAction` via constructor injection.
-   Keep controller methods slim: build DTO from validated request data, pass to action, return Resource.

---

## Migration plan for this project (step-by-step)

This plan assumes you want to convert controllers progressively, keeping tests green as you go.

1. Add directories

    - Create `app/Actions/`, `app/Services/`, and `app/DTOs/`.

2. Pick a low-risk feature to convert first (example: Posts create)

    - Implement `app/Services/PostService.php` with `create()` and `update()`.
    - Implement `app/Actions/Post/CreatePostAction.php` and `UpdatePostAction.php`.
    - Create `app/DTOs/CreatePostData.php` if you want strict typing.

3. Refactor `PostController::store()` to delegate to `CreatePostAction` (keep the endpoint signature the same so routes/tests remain valid).

4. Run tests and fix any regressions.

5. Repeat for other controller methods (update, delete, comment creation, auth register/login if desired). Keep commits small and well-scoped.

6. Optionally add `app/Contracts/PostServiceContract.php` and bind interface to implementation in `AppServiceProvider` for easier testing/mocking.

7. Move shared business logic from controllers/middleware into Services (`ImageService`, `TagService`, etc.).

8. Update tests to target Actions or Services depending on desired test granularity. Integration/feature tests can still call endpoints and should pass unchanged.

9. After migrating all endpoints, consider creating a small `app/Actions/Readme.md` documenting conventions for your team.

---

## Testing strategy

-   Unit test Services: mock repositories/filesystem and assert returned models and side-effects.
-   Unit test Actions: inject fake/mock Services and assert orchestration behaviour.
-   Keep Feature tests: they should exercise the full stack and stay mostly unchanged during refactor.

Example PHPUnit test for `CreatePostAction`:

```php
public function test_create_post_action_calls_service()
{
    $service = Mockery::mock(App\Services\PostService::class);
    $service->shouldReceive('create')->once()->andReturn(new App\Models\Post());

    $action = new App\Actions\Post\CreatePostAction($service);
    $dto = new App\DTOs\CreatePostData('t','c',null,1);
    $post = $action($dto);

    $this->assertInstanceOf(App\Models\Post::class, $post);
}
```

---

## Practical tips and pitfalls

-   Start small: convert one endpoint fully and ensure tests pass.
-   Keep controllers backward compatible while migrating.
-   Avoid duplicating validation: continue to use `FormRequest` classes and then map validated data to DTOs.
-   Prefer composition: Services can be composed of smaller services (e.g., `ImageService`) rather than one big monolith.
-   Use constructor injection for testability.

---

## Example mapping for your repo (quick)

-   `app/Http/Controllers/AuthController.php`

    -   Actions: `Auth\RegisterAction`, `Auth\LoginAction`, `Auth\LogoutAction`.
    -   Services: `AuthService` or use Laravel built-ins but keep orchestration in Actions.

-   `app/Http/Controllers/PostController.php`

    -   Actions: `Post\CreatePostAction`, `Post\UpdatePostAction`, `Post\DeletePostAction`, `Post\ListPostsAction`.
    -   Services: `PostService`, `ImageService`.

-   `app/Http/Controllers/CommentController.php`
    -   Actions: `Comment\CreateCommentAction`, `Comment\DeleteCommentAction`.
    -   Services: `CommentService`.

---

## Next steps I can take for you

-   Create the skeleton directories and a minimal `PostService` + `CreatePostAction` and update `PostController::store()` to use it (small, test-protected change).
-   Or, if you prefer, I can just create `ACTION_SERVICE_PATTERN.md` (this file) and wait for you to review.

Tell me which option you prefer and I'll start implementing the first example refactor.

---

## References & further reading

-   Articles: "Action-Domain-Responder", Patterns in Laravel apps, TDD for service layers.
-   Laravel docs: Service container, FormRequests, Resources, Events.

End of guide.
