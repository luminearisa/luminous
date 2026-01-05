<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Post;

/**
 * PostController
 * Example resource controller
 */
class PostController extends Controller
{
    /**
     * Display a listing of posts
     */
    public function index(Request $request, Response $response): void
    {
        $posts = Post::all();

        $response->success([
            'posts' => $posts,
            'total' => count($posts)
        ]);
    }

    /**
     * Store a newly created post
     */
    public function store(Request $request, Response $response): void
    {
        $user = $this->user($request);
        $data = $request->all();

        // Validate input
        $errors = $this->validate($data, [
            'title' => 'required|string|min:3',
            'content' => 'required|string|min:10'
        ]);

        if (!empty($errors)) {
            $response->validationError($errors);
            return;
        }

        // Create post
        $postData = [
            'title' => $data['title'],
            'content' => $data['content'],
            'user_id' => $user['user_id'],
        ];

        if (Post::create($postData)) {
            $postId = Post::lastId();
            $post = Post::find($postId);

            $response->created($post, 'Post created successfully');
        } else {
            $response->serverError('Failed to create post');
        }
    }

    /**
     * Display the specified post
     */
    public function show(Request $request, Response $response, array $params): void
    {
        $id = $params['id'] ?? null;

        if (!$id) {
            $response->error('Post ID is required');
            return;
        }

        $post = Post::find($id);

        if (!$post) {
            $response->notFound('Post not found');
            return;
        }

        $response->success($post);
    }

    /**
     * Update the specified post
     */
    public function update(Request $request, Response $response, array $params): void
    {
        $user = $this->user($request);
        $id = $params['id'] ?? null;

        if (!$id) {
            $response->error('Post ID is required');
            return;
        }

        $post = Post::find($id);

        if (!$post) {
            $response->notFound('Post not found');
            return;
        }

        // Check ownership
        if ($post['user_id'] != $user['user_id']) {
            $response->forbidden('You are not authorized to update this post');
            return;
        }

        $data = $request->all();

        // Validate input
        $errors = $this->validate($data, [
            'title' => 'string|min:3',
            'content' => 'string|min:10'
        ]);

        if (!empty($errors)) {
            $response->validationError($errors);
            return;
        }

        // Update post
        $updateData = [];
        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
        }
        if (isset($data['content'])) {
            $updateData['content'] = $data['content'];
        }

        if (empty($updateData)) {
            $response->error('No data to update');
            return;
        }

        if (Post::update($id, $updateData)) {
            $post = Post::find($id);
            $response->success($post, 'Post updated successfully');
        } else {
            $response->serverError('Failed to update post');
        }
    }

    /**
     * Remove the specified post
     */
    public function destroy(Request $request, Response $response, array $params): void
    {
        $user = $this->user($request);
        $id = $params['id'] ?? null;

        if (!$id) {
            $response->error('Post ID is required');
            return;
        }

        $post = Post::find($id);

        if (!$post) {
            $response->notFound('Post not found');
            return;
        }

        // Check ownership
        if ($post['user_id'] != $user['user_id']) {
            $response->forbidden('You are not authorized to delete this post');
            return;
        }

        if (Post::delete($id)) {
            $response->success(null, 'Post deleted successfully');
        } else {
            $response->serverError('Failed to delete post');
        }
    }
}
