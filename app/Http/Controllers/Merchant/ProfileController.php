<?php



declare(strict_types=1);



namespace App\Http\Controllers\Merchant;



use App\Http\Controllers\Controller;
use App\Http\Requests\Merchant\ProfilePhotoUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Services\Merchant\ProfilePhotoService;
use App\Support\UserAvatar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;



class ProfileController extends Controller

{

    public function __construct(

        private readonly ProfilePhotoService $profilePhotoService,

    ) {}



    public function edit(Request $request): View

    {

        return view('merchant.pages.profile.edit', [

            'user' => $request->user(),

        ]);

    }



    public function update(ProfileUpdateRequest $request): RedirectResponse

    {

        $request->user()->fill($request->validated());



        if ($request->user()->isDirty('email')) {

            $request->user()->email_verified_at = null;

        }



        $request->user()->save();



        return Redirect::route('profile.edit')->with('status', 'profile-updated');

    }



    public function showPhoto(Request $request, User $user): StreamedResponse
    {
        abort_unless($request->user()?->is($user), 403);

        $path = UserAvatar::photoPath($user);

        if ($path === null || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $lastModified = Storage::disk('public')->lastModified($path);

        return Storage::disk('public')->response($path, headers: [
            'Cache-Control' => 'private, max-age=3600, must-revalidate',
            'Last-Modified' => gmdate('D, d M Y H:i:s', $lastModified).' GMT',
        ]);
    }

    public function updatePhoto(ProfilePhotoUpdateRequest $request): RedirectResponse

    {

        $this->profilePhotoService->store($request->user(), $request->file('photo'));



        return Redirect::route('profile.edit')->with('status', 'profile-photo-updated');

    }



    public function destroyPhoto(Request $request): RedirectResponse

    {

        $this->profilePhotoService->delete($request->user());



        return Redirect::route('profile.edit')->with('status', 'profile-photo-removed');

    }



    public function destroy(Request $request): RedirectResponse

    {

        $request->validateWithBag('userDeletion', [

            'password' => ['required', 'current_password'],

        ]);



        $user = $request->user();



        $this->profilePhotoService->delete($user);



        Auth::logout();



        $user->delete();



        $request->session()->invalidate();

        $request->session()->regenerateToken();



        return Redirect::route('login');

    }

}


