<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\PlayerResponse;
use App\Models\Question;
use Illuminate\Http\Request;

class QuizController extends Controller
{

    public function index($round = 1)
    {
        // Fetch all questions for the current round
        $questions = Question::where('round_number', $round)->get();

        switch ($round) {
            case '1':
                $departments = Department::all()->map(function ($department) use ($round) {
                    $department->total_points = $department->totalPointsForRound($round);
                    return $department;
                });
                break;

            case '2':
                $departments = Department::where('selected_for_next_round', true)->get()->map(function ($department) use ($round) {
                    $department->total_points = $department->totalPointsForRound($round);
                    return $department;
                });
                break;

            case '3':
                $departments = Department::where('selected_for_next_round', true)->get()->map(function ($department) use ($round) {
                    $department->total_points = $department->totalPointsForRound($round);
                    return $department;
                });
                break;

            default:
                return response()->json(['message' => 'Round not available']);
        }

        // Fetch the most recently answered question from PlayerResponse
        $lastResponse = PlayerResponse::latest('created_at')->first();
        $currentDepartment = null;

        if ($lastResponse) {
            $lastDepartmentId = $lastResponse->department_id;
            $currentDepartmentIndex = $departments->search(function ($department) use ($lastDepartmentId) {
                return $department->id == $lastDepartmentId;
            });

            // Get the next department in the list (cyclic rotation)
            $nextDepartmentIndex = ($currentDepartmentIndex + 1) % $departments->count();
            $currentDepartment = $departments[$nextDepartmentIndex];
        } else {
            $currentDepartment = $departments->first();
        }

        $answeredQuestions = PlayerResponse::pluck('question_id')->toArray();

        // Check if all questions in the current round have been answered
        $totalQuestions = Question::where('round_number', $round)->count();
        $answeredQ = PlayerResponse::whereIn('question_id', $questions->pluck('id'))->distinct('question_id')->count();
        $isDone = $answeredQ >= $totalQuestions;

        return view('quiz', compact('questions', 'departments', 'currentDepartment', 'answeredQuestions', 'round', 'isDone'));
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
            'selected_answer' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'round' => 'required|integer',
            'is_answered' => 'nullable',
        ]);

        $question = Question::find($request->question_id);

        if ($request->has('is_answered') && $request->is_answered == false) {
            $isCorrect = false;
        } else {
            $isCorrect = $question->correct_answer === $request->selected_answer;
        }

        $round = $request->round;



        PlayerResponse::create([
            'department_id' => $request->department_id,
            'question_id' => $request->question_id,
            'is_correct' => $isCorrect,
            'points' => $isCorrect ? $question->point : 0
        ]);

        // Calculate the latest total points for the department
        $latestPoints = PlayerResponse::where('department_id', $request->department_id)->sum('points');


        // Check if all questions in the round have been answered by all departments
        $totalQuestions = Question::where('round_number', $round)->count();
        $answeredQuestions = PlayerResponse::distinct('question_id')->count();

        if ($answeredQuestions >= $totalQuestions) {
            // Get the top 2 departments based on total points
            $topDepartments = PlayerResponse::select('department_id')
                ->groupBy('department_id')
                ->selectRaw('SUM(points) as total_points')
                ->orderByDesc('total_points')
                ->limit(2)
                ->pluck('department_id');

            // Set status for departments
            if ($topDepartments->count() > 0) {
                Department::where('status', '!=', '1')->whereIn('id', $topDepartments)->update(['status' => 1]);
                // Department::whereNotIn('id', $topDepartments)->update(['status' => 2]);
            }

            return response()->json(['is_correct' => $isCorrect, 'points_awarded' => $isCorrect ? $question->point : 0, 'latest_points' => $latestPoints, 'nextround' => true]);
        }

        return response()->json(['is_correct' => $isCorrect, 'points_awarded' => $isCorrect ? $question->point : 0, 'latest_points' => $latestPoints]);
    }

    public function selectNextRound(Request $request)
    {
        // Reset all departments
        Department::query()->update(['selected_for_next_round' => false]);

        // Set selected departments
        if ($request->has('selected_departments')) {
            Department::whereIn('id', $request->selected_departments)->update(['selected_for_next_round' => true]);
        }

        return redirect()->back()->with('success', 'Departments selected for the next round.');
    }
}
