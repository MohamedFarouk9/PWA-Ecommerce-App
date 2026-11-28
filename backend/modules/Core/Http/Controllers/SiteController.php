<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Modules\Core\Repositories\Contracts\SiteRepositoryInterface;

class SiteController extends Controller
{
    use ResponseTrait;

    public function __construct(private SiteRepositoryInterface $siteRepository) {}

    public function getContent($type)
    {
        $content = $this->siteRepository->getContent($type);

        if (!$content) {
            return $this->response('error', 'Content not found');
        }

        return $this->successReturn(
            'Content retrieved successfully',
            [
                'content' => $content->content,
                'version' => $content->version,
            ]
        );
    }

    public function updateContent(Request $request, $type)
    {
        $request->validate([
            'content' => 'required|string|max:10000',
            'type'    => 'required|in:about,refund,privacy,purchase_guide',
        ]);

        $content = $this->siteRepository->updateContent($type, [
            'content' => $request->content,
        ]);

        return $this->successReturn(
            'Content updated successfully',
            $content
        );
    }

    public function getSettings()
    {
        $settings = $this->siteRepository->getSettings();

        return $this->successReturn(
            'Settings retrieved successfully',
            $settings
        );
    }

    public function updateSettings(Request $request)
    {
        $data = $request->all();

        $request->validate([
            'site_name'     => 'string|max:255',
            'contact_email' => 'email',
            'site_logo'     => 'string',
        ]);

        $updated = $this->siteRepository->updateSettings($data);

        return $this->successReturn(
            'Settings updated successfully',
            $updated
        );
    }
}
