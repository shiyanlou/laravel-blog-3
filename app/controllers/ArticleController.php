<?php

class ArticleController extends \BaseController {

	public function __construct()
	{
		$this->beforeFilter('auth', array('only' => array('create', 'store', 'edit', 'update', 'destroy')));
	}

	public function preview() {
		return Markdown::parse(Input::get('content'));
	}

	/**
	 * Display a listing of the resource.
	 * GET /article
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}

	/**
	 * Show the form for creating a new resource.
	 * GET /article/create
	 *
	 * @return Response
	 */
	public function create()
	{
		return View::make('articles.create');
	}

	/**
	 * Store a newly created resource in storage.
	 * POST /article
	 *
	 * @return Response
	 */
	public function store()
	{
		$rules = [
			'title'   => 'required|max:100',
			'content' => 'required',
			'tags'    => array('required', 'regex:/^\w+$|^(\w+,)+\w+$/'),
		];
		$validator = Validator::make(Input::all(), $rules);
		if ($validator->passes()) {
			$article = Article::create(Input::only('title', 'content'));
			$article->user_id = Auth::id();
			$resolved_content = Markdown::parse(Input::get('content'));
			$article->resolved_content = $resolved_content;
			$tags = explode(',', Input::get('tags'));
			if (str_contains($resolved_content, '<p>')) {
				$start = strpos($resolved_content, '<p>');
				$length = strpos($resolved_content, '</p>') - $start - 3;
				$article->summary = substr($resolved_content, $start + 3, $length);
			} else if (str_contains($resolved_content, '</h')) {
				$start = strpos($resolved_content, '<h');
				$length = strpos($resolved_content, '</h') - $start - 4;
				$article->summary = substr($resolved_content, $start + 4, $length);
			}
			$article->save();
			foreach ($tags as $tagName) {
				$tag = Tag::whereName($tagName)->first();
				if (!$tag) {
					$tag = Tag::create(array('name' => $tagName));
				}
				$tag->count++;
				$article->tags()->save($tag);
			}
			return Redirect::route('article.show', $article->id);
		} else {
			return Redirect::route('article.create')->withInput()->withErrors($validator);
		}
	}

	/**
	 * Display the specified resource.
	 * GET /article/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		return View::make('articles.show')->with('article', Article::find($id));
	}

	/**
	 * Show the form for editing the specified resource.
	 * GET /article/{id}/edit
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 * PUT /article/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 * DELETE /article/{id}
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}