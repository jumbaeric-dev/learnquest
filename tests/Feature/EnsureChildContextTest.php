<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureChildContext;
use App\Models\Child;
use App\Models\User;
use App\Services\Child\Context\CurrentChildService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureChildContextTest extends TestCase
{
  use RefreshDatabase;

  public function test_authenticated_child_can_pass_child_context(): void
  {
    $user = User::factory()->create();

    Child::factory()->create([
      "user_id" => $user->id,
      "is_active" => true,
    ]);

    $this->actingAs($user);

    $request = Request::create("/child/my-universe", "GET");

    /*
     * The middleware checks both:
     *
     * $request->user()
     * Auth::user()
     *
     * Request::create() creates a standalone request, so explicitly
     * connect it to the authenticated test user.
     */
    $request->setUserResolver(fn() => $user);

    $middleware = app(EnsureChildContext::class);

    $response = $middleware->handle(
      $request,
      fn(Request $request) => response(
        "child context allowed",
        Response::HTTP_OK
      ),
      app(CurrentChildService::class)
    );

    $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

    $this->assertSame("child context allowed", $response->getContent());
  }

  public function test_authenticated_user_without_child_is_forbidden(): void
  {
    $user = User::factory()->create();

    $this->actingAs($user);

    $request = Request::create("/child/my-universe", "GET");

    $request->setUserResolver(fn() => $user);

    $middleware = app(EnsureChildContext::class);

    try {
      $middleware->handle(
        $request,
        fn(Request $request) => response("should not be reached"),
        app(CurrentChildService::class)
      );

      $this->fail(
        "Expected EnsureChildContext to reject a user without a child."
      );
    } catch (HttpException $exception) {
      $this->assertSame(Response::HTTP_FORBIDDEN, $exception->getStatusCode());
    }
  }

  public function test_guest_is_redirected_to_login(): void
  {
    $request = Request::create("/child/my-universe", "GET");

    $middleware = app(EnsureChildContext::class);

    $response = $middleware->handle(
      $request,
      fn(Request $request) => response("should not be reached"),
      app(CurrentChildService::class)
    );

    $this->assertSame(
      Response::HTTP_FOUND,
      $response->getStatusCode()
    );

    $this->assertSame(
      route("login"),
      $response->headers->get("Location")
    );
  }
}