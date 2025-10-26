<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $q = $request->query('q');
        $companyId = $request->query('company_id');

        $query = Contact::with('company');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        if ($q) {
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('first_name', 'like', '%' . $q . '%')
                    ->orWhere('last_name', 'like', '%' . $q . '%')
                    ->orWhere('email', 'like', '%' . $q . '%');
            });
        }

        return response()->json($query->paginate($perPage));
    }

    /**
     * Display the specified resource.
     *
     * @param Contact $contact
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Contact $contact)
    {
        return response()->json(
            $contact->load('company', 'deals', 'attachments')
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'nullable|integer|exists:companies,id',
            'first_name' => 'required|string',
            'last_name' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'job_title' => 'nullable|string',
            'meta' => 'nullable|array',
        ]);

        $contact = Contact::create($data);
        return response()->json($contact, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     *
     * @param Contact $contact
     */
    public function update(Request $request, Contact $contact)
    {
        $data = $request->validate([
            'company_id' => 'nullable|integer|exists:companies,id',
            'first_name' => 'sometimes|required|string',
            'last_name' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'job_title' => 'nullable|string',
            'meta' => 'nullable|array',
        ]);

        $contact->update($data);
        return response()->json($contact);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Contact $contact
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();
        return response()->json(null, 204);
    }
}
