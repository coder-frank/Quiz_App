@extends('layouts.app')

<style>
    
    .ball {
        border-radius: 50%;
        background: #0d6efd;
        color: white;
        border: none;
        width: 50% !important;
        height: 100;
        cursor: pointer;
    }
</style>
<style>
    /* Overlay for flower burst */
    .flower-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        z-index: 1000;
        pointer-events: none;
        transition: opacity 0.3s ease-in-out;
    }

    /* Active overlay */
    .flower-overlay.active {
        opacity: 1;
        pointer-events: none;
    }

    /* Container for flower burst */
    .flower-burst {
        position: relative;
        width: 300px;
        height: 300px;
    }

    /* Flower elements */
    .flower {
        position: absolute;
        width: 50px;
        height: 50px;
        background: url('https://img.icons8.com/color/96/flower.png')  center/contain;
        opacity: 0;
        animation: burst 1.5s ease-out forwards;
    }

    /* Flower burst animation */
    @keyframes burst {
        0% {
            opacity: 0;
            transform: scale(0) translate(0, 0);
        }
        50% {
            opacity: 1;
        }
        100% {
            opacity: 0;
            transform: scale(1.5) translate(var(--x), var(--y));
        }
    }
</style>

@section('content')

<div class="flower-overlay" id="flowerOverlay">
    <div class="flower-burst" id="flowerBurst"></div>
</div>

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
                <button>
                   Get Result
                </button>
            </ul>
        </div>
        <div class="col-8">
            <h4 class="text-warning">Current Department: <span
                    id="currentDepartment">{{ $currentDepartment ? $currentDepartment->name : 'N/A' }}</span></h4>
                    <div id="questionsContainer" class="row g-3">
                        @foreach ($questions->where('round_number', 1) as $question)
                            <div class="col-3">
                                <button type="button" class="ball" id="questionButton{{ $question->id }}"
                                    onclick="fetchQuestion({{ $question->id }})"
                                    @if (in_array($question->id, $answeredQuestions)) disabled @endif>
                                    {{ $question->id }}
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


    $('#answerForm').submit(function (e) {
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
    }, function (data) {
        if (data.is_correct) {
            Swal.fire({
                icon: 'success',
                title: 'Correct!',
                text: 'Good job!'
            });

            // Update department points dynamically
            const newPoints = parseInt($(`#point${departmentId}`).text()) + data.points_awarded;
            $(`#point${departmentId}`).text(newPoints);

            // Trigger flower burst animation
            const overlay = document.getElementById("flowerOverlay");
            const burstContainer = document.getElementById("flowerBurst");

            overlay.classList.add("active");

            // Clear previous flowers
            burstContainer.innerHTML = "";

            // Create flower elements for the burst
            for (let i = 0; i < 12; i++) {
                const flower = document.createElement("div");
                flower.className = "flower";
                flower.style.setProperty("--x", `${Math.cos((i * Math.PI) / 6) * 150}px`);
                flower.style.setProperty("--y", `${Math.sin((i * Math.PI) / 6) * 150}px`);
                burstContainer.appendChild(flower);
            }

            // Remove overlay after animation ends
            setTimeout(() => {
                overlay.classList.remove("active");
            }, 2500);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Wrong!',
                text: 'Try again next time.'
            });
        }

        $(`#questionButton${questionId}`).addClass('btn-success').attr('disabled', true).text('Answered');

        // Move to the next department
        currentDepartmentIndex = (currentDepartmentIndex + 1) % departments.length;
        const nextDepartment = departments[currentDepartmentIndex];
        $('#currentDepartment').text(nextDepartment.name);
        $('#questionModal').modal('hide');
    });
});

</script>
@endsection
