<?php

namespace App\Services\Contacts;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ContactQueryService
{
    private ContactSearchService $search;
    private ContactSortingService $sorting;
    private ContactTrashFilterService $trashFilter;
    public function __construct(
        ContactSearchService $search,
        ContactSortingService $sorting,
        ContactTrashFilterService $trashFilter,
    ) {
        $this->search = $search;
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated contacts, applying filters/sorting.
     *
     * @param Request $request
     *
     * @return LengthAwarePaginator
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Contact::with('company');

        $this->search->applySearch($query, $request);
        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single contact.
     *
     * @param Contact $contact
     *
     * @return Contact
     */
    public function show(Contact $contact): Contact
    {
        return $contact->load('company', 'deals', 'attachments');
    }
}
