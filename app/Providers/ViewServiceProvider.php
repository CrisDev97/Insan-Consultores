<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\ContactMessage;

class ViewServiceProvider extends ServiceProvider
{
  public function boot(): void
  {
    View::composer('admin.*', function ($view) {
      $unreadCount = ContactMessage::whereNull('read_at')->count();
      $view->with('adminUnreadMessages', $unreadCount);
    });
  }
}
