@extends('layouts.app')
@section('content')
<div class="container">
    <h1 class="text-center mb-4 text-primary fw-bold">YABATECH INTERFALCULTY QUIZ</h1>

    <div class="row mb-4">
        <div class="col-4">
            <h4 class="text-success">Departments and Points:</h4>
            <ul class="list-group">
                @foreach ($departments as $department)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-person-fill"></i> <strong>{{ $department->name }}</strong>
                        </div>
                        <span class="badge bg-primary rounded-pill"
                            id="point{{ $department->id }}">{{ $department->total_points }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="col-8">
            <h4 class="text-warning">Current Department: <span
                    id="currentDepartment">{{ $currentDepartment ? $currentDepartment->name : 'N/A' }}</span></h4>

            <div id="questionsContainer" class="row g-3">
                @foreach ($questions as $question)
                    <div class="col-3">
                        <button class="btn btn-outline-primary w-100 fw-bold" id="questionButton{{ $question->id }}"
                            onclick="fetchQuestion({{ $question->id }})"
                            @if (in_array($question->id, $answeredQuestions)) disabled @endif>
                            Question {{ $question->id }}
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div id="questionModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="questionText">Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="answerForm">
                    <div id="optionsContainer" class="mb-3">
                        <!-- Options will be dynamically populated -->
                    </div>
                    <input type="hidden" id="departmentId" name="department_id">
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success fw-bold" id="submitAnswerButton">Submit
                            Answer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let departments = @json($departments);
    let currentDepartmentIndex = @json(
        $departments->search(function ($department) use ($currentDepartment) {
            return $department->id == $currentDepartment->id;
        }));

    function fetchQuestion(questionId) {
        const currentDepartment = departments[currentDepartmentIndex];
        const departmentId = currentDepartment.id;

        $.get(`/quiz/question/${questionId}`, function(data) {
            $('#questionText').text(data.question.question);
            const options = JSON.parse(data.question.options);
            $('#optionsContainer').empty();
            options.forEach(option => {
                $('#optionsContainer').append(`
                <div class="form-check">
                    <input type="radio" class="form-check-input" name="answer" value="${option}" required>
                    <label class="form-check-label">${option}</label>
                </div>`);
            });
            $('#departmentId').val(departmentId); // Dynamically set department ID
            $('#answerForm').data('questionId', questionId);
            $('#questionModal').modal('show');
        });
    }


    $('#answerForm').submit(function(e) {
        e.preventDefault();
        const questionId = $(this).data('questionId');
        const departmentId = $('#departmentId').val();
        const selectedAnswer = $('input[name="answer"]:checked').val();

        // Set up CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.post('/quiz/submit-answer', {
            question_id: questionId,
            department_id: departmentId,
            selected_answer: selectedAnswer,
        }, function(data) {
            if (data.is_correct) {
                Swal.fire({
                    icon: 'success',
                    title: 'Correct!',
                    text: 'Good job!'
                });

                // Update department points dynamically
                const newPoints = parseInt($(`#point${departmentId}`).text()) + data.points_awarded;
                $(`#point${departmentId}`).text(newPoints);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Wrong!',
                    text: 'Try again next time.'
                });
            }
            $(`#questionButton${questionId}`).addClass('btn-success').attr('disabled', true).text(
                'Answered');

            // Move to the next department
            currentDepartmentIndex = (currentDepartmentIndex + 1) % departments.length;
            const nextDepartment = departments[currentDepartmentIndex];
            $('#currentDepartment').text(nextDepartment.name);
            $('#questionModal').modal('hide');
        });
    });
</script>
@endsection
