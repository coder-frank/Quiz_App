@extends('layouts.app')
@section('content')
    <div class="container mt-4">
        <h1 class="text-center text-primary fw-bold">Admin Dashboard</h1>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mt-4" id="adminTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#departmentsTab">Departments</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#questionsTab">Questions</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#nextRoundTab">Next Round Selection</a>
            </li>
        </ul>

        <!-- Tabs Content -->
        <div class="tab-content mt-3">
            <!-- Departments Tab -->
            <div class="tab-pane fade show active" id="departmentsTab">
                <h3>Add Department</h3>
                <form id="addDepartmentForm">
                    @csrf
                    <div class="mb-3">
                        <input type="text" name="name" class="form-control" placeholder="Department Name" required />
                    </div>
                    <button type="submit" class="btn btn-primary">Add Department</button>
                </form>
            </div>

            <!-- Questions Tab -->
            <div class="tab-pane fade" id="questionsTab">
                <h3>Add Question</h3>
                <form id="addQuestionForm">
                    @csrf
                    <div class="mb-3">
                        <select name="round_number" class="form-select" required>
                            <option value="0" selected disabled>Select a round</option>
                            <option value="1">Round 1</option>
                            <option value="2">Round 2</option>
                            <option value="3">Round 3</option>
                            <option value="4">Bonus Question</option>
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
                        <input type="text" name="correct_answer" class="form-control" placeholder="Correct Answer" required />
                    </div>
                    <button type="submit" class="btn btn-primary">Add Question</button>
                </form>
            </div>

            <!-- Next Round Selection Tab -->
            <div class="tab-pane fade" id="nextRoundTab">
                <h3>Select Departments for Next Round</h3>
                <form id="nextRoundForm">
                    @csrf
                    <ul class="list-group">
                        @foreach ($departments as $department)
                            <li class="list-group-item">
                                <input type="checkbox" name="selected_departments[]" value="{{ $department->id }}"
                                    {{ $department->selected_for_next_round ? 'checked' : '' }}>
                                {{ $department->name }}
                            </li>
                        @endforeach
                    </ul>
                    <button type="submit" class="btn btn-primary mt-2">Confirm Selection</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="toastMessage" class="toast align-items-center text-white bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <!-- AJAX Scripts -->
    <script>
        $(document).ready(function () {
            function showToast(message) {
                $("#toastMessage .toast-body").text(message);
                const toast = new bootstrap.Toast($("#toastMessage"));
                toast.show();
            }

            // Add Department AJAX
            $("#addDepartmentForm").submit(function (e) {
                e.preventDefault();
                $.post('/admin/add-department', $(this).serialize(), function (data) {
                    showToast(data.message);
                    $("#addDepartmentForm")[0].reset();
                });
            });

            // Add Question AJAX
            $("#addQuestionForm").submit(function (e) {
                e.preventDefault();
                $.post('/admin/add-question', $(this).serialize(), function (data) {
                    showToast(data.message);
                    $("#addQuestionForm")[0].reset();
                });
            });

            // Select Departments for Next Round AJAX
            $("#nextRoundForm").submit(function (e) {
                e.preventDefault();
                $.post("{{ route('quiz.selectNextRound') }}", $(this).serialize(), function (data) {
                    showToast("Next round selection saved successfully!");
                });
            });
        });
    </script>
@endsection
