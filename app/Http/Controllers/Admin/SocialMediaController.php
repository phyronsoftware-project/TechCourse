<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialMediaLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SocialMediaController extends Controller
{
    protected string $table = 'social_media_links';

    protected array $iconOptions = [
        'fa-brands fa-facebook-f' => 'Facebook',
        'fa-brands fa-youtube' => 'YouTube',
        'fa-brands fa-telegram' => 'Telegram',
        'fa-brands fa-tiktok' => 'TikTok',
        'fa-brands fa-x-twitter' => 'X / Twitter',
        'fa-brands fa-instagram' => 'Instagram',
        'fa-brands fa-linkedin-in' => 'LinkedIn',
        'fa-brands fa-github' => 'GitHub',
        'fa-solid fa-globe' => 'Website',
    ];

    public function index(Request $request): View
    {
        $setupReady = Schema::hasTable($this->table);
        $platform = $request->string('platform')->toString();
        $status = $request->string('status')->toString();
        $search = $request->string('search')->toString();

        $links = new LengthAwarePaginator([], 0, 10);

        if ($setupReady) {
            $query = SocialMediaLink::query()
                ->orderBy('platform')
                ->orderBy('sort_order')
                ->latest('id');

            if ($platform !== '') {
                $query->where('platform', $platform);
            }

            if ($status !== '') {
                $query->where('is_active', $status === 'active');
            }

            if ($search !== '') {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('url', 'like', "%{$search}%")
                        ->orWhere('icon', 'like', "%{$search}%");
                });
            }

            $links = $query->paginate(10)->withQueryString();
        }

        return view('admin.pages.social-media.index', [
            'pageTitle' => 'Social Media Management',
            'links' => $links,
            'filters' => $request->only(['platform', 'status', 'search']),
            'setupReady' => $setupReady,
            'iconLabels' => $this->iconOptions,
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.pages.social-media.create', [
            'pageTitle' => 'Create Social Media Link',
            'defaultPlatform' => $request->string('platform')->toString() ?: 'web',
            'iconOptions' => $this->iconOptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! Schema::hasTable($this->table)) {
            return redirect()
                ->route('admin.social-media.index')
                ->with('error', 'Social media table is not created yet. Please run the SQL command first.');
        }

        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active');

        SocialMediaLink::create($data);

        return redirect()
            ->route('admin.social-media.index', ['platform' => $data['platform']])
            ->with('success', 'Social media link created successfully.');
    }

    public function edit(SocialMediaLink $social_medium): View
    {
        return view('admin.pages.social-media.edit', [
            'pageTitle' => 'Edit Social Media Link',
            'link' => $social_medium,
            'recordId' => $social_medium->id,
            'iconOptions' => $this->iconOptions,
        ]);
    }

    public function update(Request $request, SocialMediaLink $social_medium): RedirectResponse
    {
        $data = $request->validate($this->rules($social_medium));
        $data['is_active'] = $request->boolean('is_active');

        $social_medium->update($data);

        return redirect()
            ->route('admin.social-media.index', ['platform' => $data['platform']])
            ->with('success', 'Social media link updated successfully.');
    }

    public function destroy(SocialMediaLink $social_medium): RedirectResponse
    {
        $platform = $social_medium->platform;
        $social_medium->delete();

        return redirect()
            ->route('admin.social-media.index', ['platform' => $platform])
            ->with('success', 'Social media link deleted successfully.');
    }

    protected function rules(?SocialMediaLink $link = null): array
    {
        return [
            'platform' => ['required', Rule::in(['web', 'app', 'all'])],
            'name' => ['required', 'string', 'max:120'],
            'icon' => ['required', Rule::in(array_keys($this->iconOptions))],
            'url' => ['required', 'url', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
