<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);

        return response()->json(
            Task::with('assignee','creator', 'taskable')->paginate($perPage)
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Task $task
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Task $task)
    {
        return response()->json($task->load('assignee','creator','taskable'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|integer|exists:users,id',
            'created_by' => 'nullable|integer|exists:users,id',
            'taskable_type' => 'nullable|string',
            'taskable_id' => 'nullable|integer',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:pending,completed,canceled',
            'due_at' => 'nullable|date',
        ]);

        $task = Task::create($data);

        // handle polymorphic assignment if provided
        if (!empty($data['taskable_type']) && !empty($data['taskable_id'])) {
            try {
                $model = app($data['taskable_type'])->find($data['taskable_id']);
                if ($model) {
                    $model->tasks()->save($task);
                }
            } catch (\Throwable $e) {
                // ignore invalid class names
            }
        }

        return response()->json($task->load('assignee','creator','taskable'), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @param \App\Models\Task $task
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|integer|exists:users,id',
            'created_by' => 'nullable|integer|exists:users,id',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:pending,completed,canceled',
            'due_at' => 'nullable|date',
        ]);

        $task->update($data);
        return response()->json($task->fresh()->load('assignee','creator','taskable'));
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param \App\Models\Task $task
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(null, 204);
    }
}
