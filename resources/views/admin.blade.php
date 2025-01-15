@extends('layouts.app')
@section('content')
    <div class="container">
        <h1>Admin Dashboard</h1>

        <h2>Add Department</h2>
        <form id="addDepartmentForm">
            @csrf
            <div class="mb-3">
                <input type="text" name="name" class="form-control" placeholder="Department Name" required />
            </div>
            <button type="submit" class="btn btn-primary">Add Department</button>
        </form>

        <h2 class="mt-4">Add Question</h2>
        <form id="addQuestionForm">
            @csrf
            <div class="mb-3">
                <select name="round_number" class="form-select" placeholder="Round Number" required>
                    <option value="0" selected disabled>Select a round</option>
                    <option value="1">Round 1</option>
                    <option value="2">Round 2</option>
                    <option value="3">Round 3</option>
                </select>
            </div>
            <div class="mb-3">
                <input type="number" name="point" class="form-control" placeholder="Point for Question" required />
            </div>
            <div class="mb-3">
                <input type="text" name="question" class="form-control" placeholder="Question" required />
            </div>
            <div class="mb-3">
                <input type="text" name="options[]" class="form-control" placeholder="Option A" required />
            </div>
            <div class="mb-3">
                <input type="text" name="options[]" class="form-control" placeholder="Option B" required />
            </div>
            <div class="mb-3">
                <input type="text" name="options[]" class="form-control" placeholder="Option C" required />
            </div>
            <div class="mb-3">
                <input type="text" name="options[]" class="form-control" placeholder="Option D" required />
            </div>
            <div class="mb-3">
                <input type="text" name="correct_answer" class="form-control" placeholder="Correct Answer [Not option]" required />
            </div>
            <button type="submit" class="btn btn-primary">Add Question</button>
        </form>
    </div>

    <script>
        $('#addDepartmentForm').submit(function(e) {
            e.preventDefault();
            $.post('/admin/add-department', $(this).serialize(), function(data) {
                alert(data.message);
            });
        });

        $('#addQuestionForm').submit(function(e) {
            e.preventDefault();
            $.post('/admin/add-question', $(this).serialize(), function(data) {
                alert(data.message);
            });
        });
    </script>
@endsection
