<?php

namespace Modules\Offers\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Offers\Entities\Offer;

class OfferController extends Controller
{
    public function index()
    {
        return Offer::all();
    }

    public function store(Request $request)
    {
        $offer = Offer::create($request->all());
        return response()->json($offer, 201);
    }

    public function show($id)
    {
        return Offer::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $offer = Offer::findOrFail($id);
        $offer->update($request->all());
        return response()->json($offer);
    }

    public function destroy($id)
    {
        Offer::destroy($id);
        return response()->json(null, 204);
    }
}
