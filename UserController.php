<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User; 
use App\Post;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Http\Controllers\ShowController;

class UserController extends BaseController
{
	public $successStatus = 200;
	/** 
     * login api 
     * 
     * @return \Illuminate\Http\Response 
     */


	public function index()
	{
		$posts = Post::all();

		return $this->sendResponse($posts->toArray(), 'Posts retrieved successfully.');
	}

	public function store(Request $request)
	{
		$input = $request->all();

		$validator = Validator::make($input, [
			'title' => 'required',
			'body' => 'required'
		]);

		if($validator->fails()){
			return $this->sendError('Validation Error.', $validator->errors());
		}

		$post = Product::create($input);

		return $this->sendResponse($post->toArray(), 'Post created successfully.');
	}

	public function show($id)
	{
		$post = Post::find($id);

		if (is_null($post)) {
			return $this->sendError('Post not found.');
		}

		return $this->sendResponse($post->toArray(), 'Post retrieved successfully.');
	}

	public function update(Request $request, Post $post)
	{
		$input = $request->all();

		$validator = Validator::make($input, [
			'title' => 'required',
			'body' => 'required'
		]);

		if($validator->fails()){
			return $this->sendError('Validation Error.', $validator->errors());
		}

		$post->title = $input['title'];
		$post->body = $input['body'];
		$post->save();

		return $this->sendResponse($post->toArray(), 'Post updated successfully.');
	}

	public function destroy(Product $post)
	{
		$post->delete();

		return $this->sendResponse($post->toArray(), 'Post deleted successfully.');
	}

	/* ----------------------------------------------------------------------------------------- */

    public function login(){
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){ 
            $user = Auth::user();
            $success['token'] =  $user->createToken('NinjaDev')->accessToken;

            return response()->json(['success' => $success], $this->successStatus);
        }
        else{ 
            return response()->json(['error'=>'Unauthorised'], 401); 
        } 
    }
	/** 
     * Register api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function register(Request $request) 
    {
        $validator = Validator::make($request->all(), [ 
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
		if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);            
        }
		$input = $request->all(); 
        $input['password'] = bcrypt($input['password']); 
        $user = User::create($input); 
        $success['token'] =  $user->createToken('NinjaDev')->accessToken;
        $user->createToken('NinjaDev')->accessToken;
        $success['name'] =  $user->name;
		return response()->json(['success'=>$success], $this->successStatus);
    }
	/** 
     * details api 
     * 
     * @return \Illuminate\Http\Response 
     */
	protected function details()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }

	public function local()
	{
		$user = Auth::user();
		$get_all_post_by_user_id = Post::where('user_id',$user->id)->get();
		return response()->json(['success' => $get_all_post_by_user_id], $this->successStatus);
	}

	function create_post(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'title' => ['required', 'string', 'max:255'],
			'body' => ['required', 'string',  'max:255'],
		]);
		if ($validator->fails()) {
			return response()->json(['error'=>$validator->errors()], 401);
		}
		$input = $request->all();
		$user = Auth::user();
		$get_posts_by_user_id_title = Post::where('user_id',$user->id)->where('title',$input['title'])->first();
		if(!$get_posts_by_user_id_title) {
			$post = new Post();
			$post->title = $input['title'];
			$post->body = $input['body'];
			$post->user_id = $user->id;
			$post->save();
			$success = Post::where('user_id',$user->id)->get();
		} else {
			$success = false;
		}

		return response()->json(['success'=>$success], $this->successStatus);
	}
}