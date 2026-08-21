<?php

namespace App\Services\Child\Context;

use App\Models\Child;
use Illuminate\Support\Facades\Auth;

class CurrentChildService
{
  /**
   * Return the currently active child.
   *
   * For a child account, the authenticated user's
   * associated Child is the current context.
   *
   * Parent child-switching will be handled here later.
   */
  public function current(): ?Child
  {
    $user = Auth::user();

    if (!$user) {
      return null;
    }

    return $user->child;
  }
}
