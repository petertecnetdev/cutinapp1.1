<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Blog, Event, Production, Interaction};
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::all();
        return view('blogs.index', compact('blogs'));
    }

    public function create()
    {
        return view('blogs.create');
    }

    public function store(Request $request)
{
    // Validating the form data
    $validatedData = $request->validate([
        'title' => 'required|string|max:255',
        'category' => 'required|string|max:255',
        'content' => 'required|string',
        'qa_question.*' => 'required|string', // Validate each question input
        'qa_answer.*' => 'required|string', // Validate each answer input
        'user_id' => 'required|integer',
    ]);

    // Create a new blog instance
    $blog = new Blog();
    $blog->title = $validatedData['title'];
    $blog->category = $validatedData['category'];
    $blog->tags = $request['tags'];
    $blog->content = $validatedData['content'];
    $blog->user_id = $validatedData['user_id'];

    // Save the cover image
    if ($request->hasFile('cover_image')) {
        $coverImage = $request->file('cover_image');
        $coverImageFileName = 'cover_image-' . Str::slug($blog->title) . '-' . $blog->id . '-' . now()->timestamp . '.' . $coverImage->getClientOriginalExtension();
        $coverImagePath = public_path('img/blog/' . Str::slug($blog->title) . '-' . $blog->id);
    
        if (!File::isDirectory($coverImagePath)) {
            File::makeDirectory($coverImagePath, 0777, true);
        }
    
        // Resize the image to 600x800 pixels
        $image = Image::make($coverImage);
        $image->fit(855, 500);
        $image->save($coverImagePath . '/' . $coverImageFileName);
    
        $blog->cover_image = 'img/blog/' . Str::slug($blog->title) . '-' . $blog->id . '/' . $coverImageFileName;
    }
    if ($request->input('qa_question'))
    {
// Process and save the FAQs
$questions = $validatedData['qa_question'];
$answers = $validatedData['qa_answer'];

$faqs = [];
foreach ($questions as $index => $question) {
    $faq = [
        'question' => $question,
        'answer' => $answers[$index],
    ];
    $faqs[] = $faq;
}

$blog->qa = json_encode($faqs);
    }
    

    $slug = Str::slug($request->input('title'));

    $count = Blog::where('slug', $slug)->count();
    if ($count > 0) {
        $slug = $slug . '-' . ($count + 1);
    }
    $blog->slug =$slug;
    $blog->save();

    return redirect()->route('blog.index')->with('success', 'Blog criado com sucesso!');
}

public function show($slug)
{
    $blog = Blog::where('slug', $slug)->firstOrFail();
    $blogs = Blog::all();
    $productions = Production::all();
    
    $currentDate = now(); // Obtém a data atual
    $events = Event::where('start_date', '>', $currentDate)
    ->orderBy('start_date', 'desc')
    ->get();
    $userHasLikedPost = false; // Default value

    // Check if the user is authenticated
    if (Auth::check()) {
        $user = Auth::user();
        $interaction = Interaction::where([
            'user_id' => $user->id,
            'entity_id' => $blog->id,
            'entity_type' => 'blog',
            'interaction_type' => 'like'
        ])->first();

        $interaction = new Interaction();
        $interaction->user_id =  $user->id;
        $interaction->interaction_type = 'view';
        $interaction->entity_id = $blog->id;
        $interaction->entity_type = 'blog';
        $interaction->save();
        if ($interaction) {
            $userHasLikedPost = true;
        }
    }

    $views = Interaction::where([
        'entity_id' => $blog->id,
        'entity_type' => 'blog',
        'interaction_type' => 'view'
    ])->count();
    return view('blogs.show', [
'blogs' => $blogs,
'events' => $events,
'productions' => $productions,
        'blog' => $blog,
        'liked' => $userHasLikedPost,
    ]);
}

    public function edit($slug)
    {
        $blog = Blog::where('slug', $slug)->firstOrFail();
        
        return view('blogs.edit', compact('blog'));
    }

    public function update(Request $request, $slug)
    {
        $blog = Blog::where('slug', $slug)->firstOrFail();
    
        $request->validate([
            'title' => 'required|unique:blogs,title,' . $blog->id,
            'category' => 'required',
            'content' => 'required',
        ]);
    
        $blog->title = $request->title;
        $blog->category = $request->category;
        $blog->tags = json_encode($request->tags);
        $blog->content = $request->content;
    
        if ($request->hasFile('cover_image')) {
            // Deletar a imagem antiga, se existir
            if ($blog->cover_image) {
                $oldImagePath = public_path($blog->cover_image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
    
            $coverImage = $request->file('cover_image');
            $coverImageFileName = 'cover_image-' . Str::slug($blog->title) . '-' . $blog->id . '-' . uniqid() . '.' . $coverImage->getClientOriginalExtension();
            $coverImagePath = public_path('img/blog/');
    
            // Resize the image to 600x800 pixels
            $image = Image::make($coverImage);
            $image->fit(855, 500);
            $image->save($coverImagePath . $coverImageFileName);
    
            $blog->cover_image = 'img/blog/' . $coverImageFileName;
        }
    
        // Restante do seu código de atualização
    
        $blog->save();
    
        return redirect()->route('blog.index')->with('success', 'Blog atualizado com sucesso!');
    }
    



    public function destroy($slug)
    {
        $blog = Blog::where('slug', $slug)->firstOrFail();

       

        $blog->delete();

        return redirect()->route('blog.index')->with('success', 'Blog excluído com sucesso!');
    }
}
