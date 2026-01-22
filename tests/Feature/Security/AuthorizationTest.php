<?php

use App\Filament\Resources\AdminUsers\AdminUserResource;
use App\Filament\Resources\Gigs\GigResource;
use App\Filament\Resources\Instruments\InstrumentResource;
use App\Filament\Resources\Musicians\MusicianResource;
use App\Filament\Resources\Regions\RegionResource;
use App\Filament\Resources\Tags\TagResource;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Instrument;
use App\Models\User;

test('it blocks musician from admin routes via direct URL', function () {
    $musician = User::factory()->musician()->create();

    $this->actingAs($musician);

    // Test all admin resource routes
    $this->get('/admin')->assertForbidden();
    $this->get(GigResource::getUrl('index'))->assertForbidden();
    $this->get(GigResource::getUrl('create'))->assertForbidden();
    $this->get(MusicianResource::getUrl('index'))->assertForbidden();
    $this->get(MusicianResource::getUrl('create'))->assertForbidden();
    $this->get(InstrumentResource::getUrl('index'))->assertForbidden();
    $this->get(RegionResource::getUrl('index'))->assertForbidden();
    $this->get(TagResource::getUrl('index'))->assertForbidden();
    $this->get(AdminUserResource::getUrl('index'))->assertForbidden();
});

test('it blocks musician from admin gig resource via direct URL', function () {
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->create();

    $this->actingAs($musician);

    $this->get(GigResource::getUrl('view', ['record' => $gig]))->assertForbidden();
    $this->get(GigResource::getUrl('edit', ['record' => $gig]))->assertForbidden();
});

test('it blocks musician from admin musician resource via direct URL', function () {
    $musician = User::factory()->musician()->create();
    $otherMusician = User::factory()->musician()->create();

    $this->actingAs($musician);

    $this->get(MusicianResource::getUrl('edit', ['record' => $otherMusician]))->assertForbidden();
});

test('it blocks musician from viewing other musician gig assignment via direct URL', function () {
    $musician = User::factory()->musician()->create();
    $otherMusician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();
    $gig = Gig::factory()->active()->future()->create();

    GigAssignment::factory()->pending()->create([
        'user_id' => $otherMusician->id,
        'gig_id' => $gig->id,
        'instrument_id' => $instrument->id,
    ]);

    $this->actingAs($musician);

    $this->get(route('portal.gigs.show', $gig))->assertForbidden();
});

test('it blocks musician from accepting other musician assignment via direct URL', function () {
    $musician = User::factory()->musician()->create();
    $otherMusician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();
    $gig = Gig::factory()->active()->future()->create();

    GigAssignment::factory()->pending()->create([
        'user_id' => $otherMusician->id,
        'gig_id' => $gig->id,
        'instrument_id' => $instrument->id,
    ]);

    $this->actingAs($musician);

    $this->post(route('portal.gigs.accept', $gig))->assertForbidden();
});

test('it blocks musician from declining other musician assignment via direct URL', function () {
    $musician = User::factory()->musician()->create();
    $otherMusician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();
    $gig = Gig::factory()->active()->future()->create();

    GigAssignment::factory()->pending()->create([
        'user_id' => $otherMusician->id,
        'gig_id' => $gig->id,
        'instrument_id' => $instrument->id,
    ]);

    $this->actingAs($musician);

    $this->post(route('portal.gigs.decline', $gig), ['reason' => 'Test'])->assertForbidden();
});

test('it blocks musician from requesting sub-out on other musician assignment via direct URL', function () {
    $musician = User::factory()->musician()->create();
    $otherMusician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();
    $gig = Gig::factory()->active()->future()->create();

    GigAssignment::factory()->accepted()->create([
        'user_id' => $otherMusician->id,
        'gig_id' => $gig->id,
        'instrument_id' => $instrument->id,
    ]);

    $this->actingAs($musician);

    $this->post(route('portal.gigs.subout', $gig), ['reason' => 'Emergency'])->assertForbidden();
});

test('it blocks unauthenticated access to all admin routes', function () {
    $gig = Gig::factory()->create();

    $this->get('/admin')->assertRedirect(route('filament.admin.auth.login'));
    $this->get(GigResource::getUrl('index'))->assertRedirect(route('filament.admin.auth.login'));
    $this->get(GigResource::getUrl('create'))->assertRedirect(route('filament.admin.auth.login'));
    $this->get(GigResource::getUrl('view', ['record' => $gig]))->assertRedirect(route('filament.admin.auth.login'));
    $this->get(GigResource::getUrl('edit', ['record' => $gig]))->assertRedirect(route('filament.admin.auth.login'));
    $this->get(MusicianResource::getUrl('index'))->assertRedirect(route('filament.admin.auth.login'));
    $this->get(InstrumentResource::getUrl('index'))->assertRedirect(route('filament.admin.auth.login'));
    $this->get(RegionResource::getUrl('index'))->assertRedirect(route('filament.admin.auth.login'));
    $this->get(TagResource::getUrl('index'))->assertRedirect(route('filament.admin.auth.login'));
    $this->get(AdminUserResource::getUrl('index'))->assertRedirect(route('filament.admin.auth.login'));
});

test('it blocks unauthenticated access to all portal routes', function () {
    $gig = Gig::factory()->create();

    $this->get('/portal')->assertRedirect(route('login'));
    $this->get(route('portal.dashboard'))->assertRedirect(route('login'));
    $this->get(route('portal.gigs.show', $gig))->assertRedirect(route('login'));
    $this->get(route('portal.gigs.past'))->assertRedirect(route('login'));
    $this->get(route('portal.profile'))->assertRedirect(route('login'));
    $this->post(route('portal.gigs.accept', $gig))->assertRedirect(route('login'));
    $this->post(route('portal.gigs.decline', $gig))->assertRedirect(route('login'));
    $this->post(route('portal.gigs.subout', $gig))->assertRedirect(route('login'));
});

test('it blocks unauthenticated access to admin worksheet route', function () {
    $gig = Gig::factory()->create();

    $this->get(route('admin.gigs.worksheet', $gig))->assertRedirect(route('login'));
});

test('it blocks unauthenticated access to settings routes', function () {
    $this->get('/settings/profile')->assertRedirect(route('login'));
    $this->get('/settings/password')->assertRedirect(route('login'));
    $this->get('/settings/appearance')->assertRedirect(route('login'));
});

test('it enforces CSRF protection is enabled for portal routes', function () {
    // Verify that CSRF middleware is in the web middleware group
    // This is a structural test that confirms the middleware exists
    $app = app();
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

    // Get the middleware groups
    $middlewareGroups = $kernel->getMiddlewareGroups();

    // Verify web middleware group exists and contains VerifyCsrfToken
    expect($middlewareGroups)->toHaveKey('web');

    // Check that CSRF verification is applied (VerifyCsrfToken or ValidateCsrfToken)
    $webMiddleware = $middlewareGroups['web'];
    $hasCsrfMiddleware = collect($webMiddleware)->contains(function ($middleware) {
        return str_contains($middleware, 'Csrf') || str_contains($middleware, 'VerifyCsrf');
    });

    expect($hasCsrfMiddleware)->toBeTrue();
});

test('it applies web middleware to portal routes', function () {
    // Verify that portal routes use the web middleware group which includes CSRF
    $routes = app('router')->getRoutes();

    foreach ($routes as $route) {
        if (str_starts_with($route->uri(), 'portal')) {
            $middlewares = $route->gatherMiddleware();
            expect($middlewares)->toContain('web');
        }
    }
});

test('it restricts file uploads to PDF types only in model', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $gig = Gig::factory()->create();

    // Create a real temporary PDF file with content
    $tempPath = sys_get_temp_dir().'/test_document_'.uniqid().'.pdf';
    file_put_contents($tempPath, '%PDF-1.4 test content');

    $gig->addMedia($tempPath)
        ->toMediaCollection('attachments');

    expect($gig->getMedia('attachments'))->toHaveCount(1);

    // Cleanup
    $gig->clearMediaCollection('attachments');
});

test('it rejects non-PDF file uploads in model', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $gig = Gig::factory()->create();

    // Create a real temporary image file
    $tempPath = sys_get_temp_dir().'/test_photo_'.uniqid().'.jpg';
    file_put_contents($tempPath, 'fake image content');

    $this->expectException(\Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection::class);

    $gig->addMedia($tempPath)
        ->toMediaCollection('attachments');
});

test('it rejects executable files in model', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $gig = Gig::factory()->create();

    // Create a real temporary executable file
    $tempPath = sys_get_temp_dir().'/test_malware_'.uniqid().'.exe';
    file_put_contents($tempPath, 'MZ fake executable content');

    $this->expectException(\Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection::class);

    $gig->addMedia($tempPath)
        ->toMediaCollection('attachments');
});

test('it rejects text files in model', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $gig = Gig::factory()->create();

    // Create a real temporary text file
    $tempPath = sys_get_temp_dir().'/test_readme_'.uniqid().'.txt';
    file_put_contents($tempPath, 'This is a text file content');

    $this->expectException(\Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection::class);

    $gig->addMedia($tempPath)
        ->toMediaCollection('attachments');
});

test('it rejects HTML files in model', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $gig = Gig::factory()->create();

    // Create a real temporary HTML file
    $tempPath = sys_get_temp_dir().'/test_page_'.uniqid().'.html';
    file_put_contents($tempPath, '<html><body>Hello World</body></html>');

    $this->expectException(\Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection::class);

    $gig->addMedia($tempPath)
        ->toMediaCollection('attachments');
});

test('it blocks admin from accessing musician portal', function () {
    $admin = User::factory()->admin()->create();
    $gig = Gig::factory()->create();

    $this->actingAs($admin);

    $this->get('/portal')->assertForbidden();
    $this->get(route('portal.dashboard'))->assertForbidden();
    $this->get(route('portal.gigs.past'))->assertForbidden();
    $this->get(route('portal.profile'))->assertForbidden();
    $this->get(route('portal.gigs.show', $gig))->assertForbidden();
});

test('it blocks inactive user from accessing any protected routes', function () {
    $musician = User::factory()->musician()->inactive()->create();

    $this->actingAs($musician);

    // The EnsureUserIsActive middleware logs out inactive users
    $this->get('/portal')->assertRedirect(route('login'));
    $this->assertGuest();
});

test('it allows admin to access admin worksheet for gig they can view', function () {
    $admin = User::factory()->admin()->create();
    $gig = Gig::factory()->create();

    $this->actingAs($admin);

    $this->get(route('admin.gigs.worksheet', $gig))->assertOk();
});

test('it blocks musician from accessing admin worksheet', function () {
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->create();

    $this->actingAs($musician);

    $this->get(route('admin.gigs.worksheet', $gig))->assertForbidden();
});

test('it allows musician to view their own assignment', function () {
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();
    $gig = Gig::factory()->active()->future()->create();

    GigAssignment::factory()->pending()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
        'instrument_id' => $instrument->id,
    ]);

    $this->actingAs($musician);

    $this->get(route('portal.gigs.show', $gig))->assertOk();
});

test('it allows musician to interact with their own assignments only', function () {
    $musician = User::factory()->musician()->create();
    $otherMusician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    $ownGig = Gig::factory()->active()->future()->create();
    $otherGig = Gig::factory()->active()->future()->create();

    GigAssignment::factory()->pending()->create([
        'user_id' => $musician->id,
        'gig_id' => $ownGig->id,
        'instrument_id' => $instrument->id,
    ]);

    GigAssignment::factory()->pending()->create([
        'user_id' => $otherMusician->id,
        'gig_id' => $otherGig->id,
        'instrument_id' => $instrument->id,
    ]);

    $this->actingAs($musician);

    // Can access own assignment
    $this->get(route('portal.gigs.show', $ownGig))->assertOk();
    $this->post(route('portal.gigs.accept', $ownGig))->assertRedirect();

    // Cannot access other's assignment
    $this->get(route('portal.gigs.show', $otherGig))->assertForbidden();
    $this->post(route('portal.gigs.accept', $otherGig))->assertForbidden();
});
