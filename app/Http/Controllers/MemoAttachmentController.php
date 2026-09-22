<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use App\Models\MemoAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MemoAttachmentController extends Controller
{
    public function store(Request $request, Memo $memo): RedirectResponse
    {
        abort_unless($memo->user_id === $request->user()->id, 404);

        $data = $request->validate([
            'attachments' => ['required', 'array', 'max:8'],
            'attachments.*' => ['file', 'max:5120', 'mimes:jpg,jpeg,png,gif,webp,pdf,txt,md,json,csv,zip'],
        ]);

        foreach ($data['attachments'] as $file) {
            $memo->attachments()->create([
                'user_id' => $request->user()->id,
                'name' => mb_substr($file->getClientOriginalName(), 0, 255),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
                'data' => $file->getContent(),
            ]);
        }

        return back()->with('success', 'Fichier(s) ajouté(s).');
    }

    // Types qu'on peut laisser le navigateur afficher directement.
    // Les autres (texte, JSON, CSV...) sont toujours proposés en téléchargement :
    // sans ça, un fichier texte contenant du HTML pourrait être interprété par
    // un navigateur qui ignore le Content-Type déclaré (sniffing).
    private const INLINE_SAFE = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];

    public function show(Request $request, MemoAttachment $attachment): Response
    {
        abort_unless($attachment->user_id === $request->user()->id, 404);

        $disposition = in_array($attachment->mime_type, self::INLINE_SAFE, true) ? 'inline' : 'attachment';

        return response($attachment->data, 200, [
            'Content-Type' => $attachment->mime_type,
            'Content-Length' => (string) $attachment->size,
            'Content-Disposition' => $disposition . '; filename="' . addslashes($attachment->name) . '"',
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function download(Request $request, MemoAttachment $attachment): Response
    {
        abort_unless($attachment->user_id === $request->user()->id, 404);

        return response($attachment->data, 200, [
            'Content-Type' => $attachment->mime_type,
            'Content-Length' => (string) $attachment->size,
            'Content-Disposition' => 'attachment; filename="' . addslashes($attachment->name) . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Request $request, MemoAttachment $attachment): RedirectResponse
    {
        abort_unless($attachment->user_id === $request->user()->id, 404);
        $attachment->delete();

        return back()->with('success', 'Fichier supprimé.');
    }
}