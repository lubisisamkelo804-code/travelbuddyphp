<?php

namespace App\Http\Controllers;

use App\Models\Interest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user()->load(['interests', 'posts.media', 'posts.destination', 'reviews']);

        return view('profile', [
            'user' => $user,
            'interests' => Interest::orderBy('name')->get(),
            'myDestinations' => $user->isOwner() ? $user->destinations()->latest()->get() : collect(),
            'myEvents' => $user->isCreator() ? $user->events()->latest()->get() : collect(),
        ]);
    }

    public function update(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|min:2|max:100',
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'website' => 'nullable|url|max:300',
            'social_instagram' => 'nullable|url|max:300',
            'social_facebook' => 'nullable|url|max:300',
            'social_x' => 'nullable|url|max:300',
            'budget_level' => 'required|integer|min:1|max:3',
            'interests' => 'nullable|array',
            'interests.*' => 'exists:interests,id',
        ]);

        $user = Auth::user();

        if ($r->hasFile('avatar')) {
            $user->avatar_path = $r->file('avatar')->store('avatars', 'public');
        }

        $user->fill(collect($data)->except(['interests', 'avatar'])->all());
        $user->save();
        $user->interests()->sync($data['interests'] ?? []);

        return back()->with('success', 'Profile updated.');
    }

    public function publicPosts(User $user)
    {
        return view('owners.show', [
            'user' => $user->load('interests'),
            'posts' => $user->posts()->with(['media', 'destination', 'event', 'author'])->latest()->paginate(9),
            'destinations' => $user->destinations()->with('interests')->get(),
        ]);
    }
}