<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Http\Resources\MovieResource;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    // 1. Get All Movies (Latest First)
    public function index()
    {
        $movies = Movie::where('is_published', true)
            ->with('genres') // Genre တွေပါ တွဲခေါ်မယ်
            ->latest()
            ->paginate(12); // တစ်ခါခေါ်ရင် ၁၂ ကားပြမယ်

        return MovieResource::collection($movies);
    }

    // 2. Get Single Movie Detail
    public function show($slug)
    {
        $movie = Movie::where('slug', $slug)
            ->where('is_published', true)
            ->with('genres')
            ->firstOrFail();

        return new MovieResource($movie);
    }
    
    // 3. Search Movies
    public function search(Request $request)
    {
        $query = $request->input('query');
        
        $movies = Movie::where('is_published', true)
            ->where('title', 'like', "%{$query}%")
            ->latest()
            ->take(20)
            ->get();
            
        return MovieResource::collection($movies);
    }
}