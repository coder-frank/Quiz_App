<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Question;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(){
        $departments = Department::all();
        return view('admin', compact('departments'));
    }

    public function addDepartment(Request $request) {
        $request->validate(['name' => 'required|string|unique:departments']);
        Department::create(['name' => $request->name]);
        return response()->json(['message' => 'Department added successfully']);
    }
    
    public function addQuestion(Request $request) {
        $request->validate([
            'round_number' => 'required|in:1,2,3',
            'point' => 'required|int',
            'question' => 'required|string',
            'options' => 'required|array',
            'correct_answer' => 'required|string',
        ]);
    
        Question::create([
            'point' => $request->point,
            'round_number' => $request->round_number,
            'question' => $request->question,
            'options' => json_encode($request->options),
            'correct_answer' => $request->correct_answer,
        ]);
    
        return response()->json(['message' => 'Question added successfully']);
    }
}
