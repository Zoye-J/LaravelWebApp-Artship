<?php

namespace App\Http\Controllers;

use App\Models\Key;
use App\Services\ECCEncryptionService;
use App\Services\HashingService;
use App\Services\RSAEncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class KeyManagementController extends Controller
{
    public function __construct(
        private RSAEncryptionService $rsa,
        private ECCEncryptionService $ecc,
        private HashingService $hasher,
    ) {
    }

    
    public function index()
    {
        $keys = Key::orderByDesc('created_at')->paginate(20);

        return view('admin.keys.index', compact('keys'));
    }

    
    public function create()
    {
        return view('admin.keys.create');
    }

    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'algorithm' => 'required|in:rsa,ecc',
            'purpose'   => 'required|string|max:100',
            'key_size'  => 'nullable|integer|in:1024,2048,3072,4096',
        ]);

        $key = DB::transaction(function () use ($validated) {
            
            Key::forPurpose($validated['algorithm'], $validated['purpose'])
                ->active()
                ->update(['status' => 'rotated', 'rotated_at' => now()]);

            return $this->generateAndPersist(
                $validated['algorithm'],
                $validated['purpose'],
                $validated['key_size'] ?? 2048
            );
        });

        return redirect()
            ->route('admin.keys.show', $key)
            ->with('status', strtoupper($validated['algorithm']) . ' key generated and activated.');
    }

    
    public function show(Key $key)
    {
        return view('admin.keys.show', compact('key'));
    }

    
    public function rotate(Key $key)
    {
        $newKey = DB::transaction(function () use ($key) {
            $newKey = $this->generateAndPersist($key->algorithm, $key->purpose, $key->key_size, $key->id);

            $key->update([
                'status' => 'rotated',
                'rotated_at' => now(),
            ]);

            return $newKey;
        });

        return redirect()
            ->route('admin.keys.show', $newKey)
            ->with('status', 'Key rotated. The previous key is kept (status: rotated) so data already encrypted under it can still be decrypted.');
    }

    
    public function exportPublicKey(Key $key)
    {
        return response($key->public_key, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="key-' . $key->id . '-' . $key->algorithm . '-public.json"',
        ]);
    }

    public function revoke(Key $key)
    {
        $key->update(['status' => 'revoked']);

        return back()->with('status', 'Key revoked.');
    }

    private function generateAndPersist(string $algorithm, string $purpose, ?int $keySize, ?int $rotatedFromId = null): Key
    {
        if ($algorithm === 'rsa') {
            $pair = $this->rsa->generateKeyPair($keySize ?? 2048);
            $publicJson = $this->rsa->exportPublicKey($pair);
            $privateJson = $this->rsa->exportPrivateKey($pair);
        } else {
            $pair = $this->ecc->generateKeyPair();
            $publicJson = $this->ecc->exportPublicKey($pair);
            $privateJson = $this->ecc->exportPrivateKey($pair);
            $keySize = null; // fixed by the curve (secp256k1), not user-selectable
        }

        $previousVersion = Key::forPurpose($algorithm, $purpose)->max('version') ?? 0;

        return Key::create([
            'algorithm' => $algorithm,
            'purpose' => $purpose,
            'key_size' => $keySize,
            'public_key' => $publicJson,
            'private_key' => $privateJson,
            'fingerprint' => $this->hasher->sha256($publicJson),
            'version' => $previousVersion + 1,
            'status' => 'active',
            'rotated_from_id' => $rotatedFromId,
            'generated_by' => Auth::id(),
        ]);
    }
}
