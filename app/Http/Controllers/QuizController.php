<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\PlayerResponse;
use App\Models\Question;
use Illuminate\Http\Request;

class QuizController extends Controller
{

    public function index($round)
    {
        // Fetch all questions
        $questions = Question::all();

        // Fetch all departments and calculate their total points
        $departments = Department::all()->map(function ($department) {
            $department->total_points = $department->totalPoints();
            return $department;
        });

        // Fetch the most recently answered question from PlayerResponse
        $lastResponse = PlayerResponse::latest('created_at')->first();
        $currentDepartment = null;

        if ($lastResponse) {
            // Find the department after the most recent response's department
            $lastDepartmentId = $lastResponse->department_id;
            $currentDepartmentIndex = $departments->search(function ($department) use ($lastDepartmentId) {
                return $department->id == $lastDepartmentId;
            });

            // Get the next department in the list (cyclic rotation)
            $nextDepartmentIndex = ($currentDepartmentIndex + 1) % $departments->count();
            $currentDepartment = $departments[$nextDepartmentIndex];
        } else {
            // If no responses yet, default to the first department
            $currentDepartment = $departments->first();
        }

        // Fetch answered questions (question IDs)
        $answeredQuestions = PlayerResponse::pluck('question_id')->toArray();

        return view('quiz', compact('questions', 'departments', 'currentDepartment', 'answeredQuestions'));
    }





    public function getQuestion($id)
    {
        $question = Question::find($id);
        if (!$question) return response()->json(['error' => 'Question not found'], 404);

        return response()->json(["question" => $question]);
    }

    public function submitAnswer(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'selected_answer' => 'required|string',
            'department_id' => 'required|exists:departments,id'
        ]);

        $question = Question::find($request->question_id);
        $isCorrect = $question->correct_answer === $request->selected_answer;

        PlayerResponse::create([
            'department_id' => $request->department_id,
            'question_id' => $request->question_id,
            'is_correct' => $isCorrect,
            'points' => $isCorrect ? $question->point : 0
        ]);

        // Calculate the latest total points for the department
        $latestPoints = PlayerResponse::where('department_id', $request->department_id)->sum('points');

        return response()->json(['is_correct' => $isCorrect, 'points_awarded' => $isCorrect ? $question->point : 0, 'latest_points' => $latestPoints]);
    }
}
