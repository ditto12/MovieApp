<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MovieController extends Controller
{
    public function index()
    {
        $movies = Movie::paginate(5);
        return view('admin.movies.index', compact('movies'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.movies.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'vote_average' => 'required|numeric|min:1|max:10',
            'youtube_link' => 'nullable|url',
            'description' => 'nullable|string|min:50|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'video' => 'nullable|mimes:mp4,mov,ogg,qt|max:20000',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $movie = new Movie();
        $movie->title = $request->title;
        $movie->vote_average = $request->vote_average;
        $movie->youtube_link = $request->youtube_link;
        $movie->description = $request->description;

        // Resmi otomatik olarak 300x450 boyutlarına dönüştür
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('images', 'public');
            
            $manager = new ImageManager(new Driver());
            $manager->read(storage_path('app/public/' . $imagePath))
                ->cover(300, 450)
                ->save(storage_path('app/public/' . $imagePath));
                
            $movie->image = $imagePath;
        }

        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('videos', 'public');
            $movie->video = $videoPath;
        }

        if ($request->hasFile('poster')) {
            $posterPath = $request->file('poster')->store('posters', 'public');
            
            // Poster'ı 16:9 oranında yeniden boyutlandır (1280x720)
            $manager = new ImageManager(new Driver());
            $manager->read(storage_path('app/public/' . $posterPath))
                ->cover(1280, 720)
                ->save(storage_path('app/public/' . $posterPath));
                
            $movie->poster = $posterPath;
        }

        $movie->save();

        if ($request->has('categories')) {
            $movie->categories()->sync($request->categories);
        }

        return redirect()->route('admin.movies.index')->with('success', 'Movie created successfully.');
    }

    public function edit($id)
    {
        $movie = Movie::findOrFail($id);
        $categories = Category::all();
        return view('admin.movies.edit', compact('movie', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'nullable|string|min:50|max:250',
            'vote_average' => 'nullable|numeric|min:0|max:10',
            'youtube_link' => 'nullable|url',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'video' => 'nullable|mimes:mp4,mov,ogg,qt|max:2000000',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $movie = Movie::findOrFail($id);

        // Resmi otomatik olarak 300x450 boyutlarına dönüştür
        if ($request->hasFile('image')) {
            if ($movie->image) {
                Storage::disk('public')->delete($movie->image);
            }
            $image = $request->file('image');
            $imagePath = $image->store('images', 'public');
            
            $manager = new ImageManager(new Driver());
            $manager->read(storage_path('app/public/' . $imagePath))
                ->cover(300, 450)
                ->save(storage_path('app/public/' . $imagePath));
                
            $movie->image = $imagePath;
        }

        if ($request->hasFile('video')) {
            if ($movie->video) {
                Storage::disk('public')->delete($movie->video);
            }
            $videoPath = $request->file('video')->store('videos', 'public');
            $movie->video = $videoPath;
        }

        if ($request->hasFile('poster')) {
            if ($movie->poster) {
                Storage::disk('public')->delete($movie->poster);
            }
            $posterPath = $request->file('poster')->store('posters', 'public');
            
            // Poster'ı 16:9 oranında yeniden boyutlandır (1280x720)
            $manager = new ImageManager(new Driver());
            $manager->read(storage_path('app/public/' . $posterPath))
                ->cover(1280, 720)
                ->save(storage_path('app/public/' . $posterPath));
                
            $movie->poster = $posterPath;
        }

        $movie->update([
            'title' => $request->title,
            'description' => $request->description,
            'vote_average' => $request->vote_average,
            'youtube_link' => $request->youtube_link,
            'image' => $movie->image,
            'video' => $movie->video,
            'poster' => $movie->poster,
        ]);

        $movie->categories()->sync($request->categories);

        return redirect()->route('admin.movies.index')->with('success', 'Movie updated successfully.');
    }

    public function destroy($id)
    {
        $movie = Movie::findOrFail($id);
        if ($movie->image) {
            Storage::disk('public')->delete($movie->image);
        }
        if ($movie->video) {
            Storage::disk('public')->delete($movie->video);
        }
        if ($movie->poster) {
            Storage::disk('public')->delete($movie->poster);
        }
        $movie->delete();

        return redirect()->route('admin.movies.index')->with('success', 'Movie deleted successfully.');
    }
}
