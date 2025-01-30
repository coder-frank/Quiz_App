@extends('layouts.app')

@section('content')
    <!-- Animated Floating Background -->
    <div class="floating-box"></div>
    <div class="floating-box"></div>
    <div class="floating-box"></div>
    <div class="floating-box"></div>
    <div class="floating-box"></div>
    <div class="floating-box"></div>

    <!-- Flower Burst Animation -->
    <div class="flower-overlay" id="flowerOverlay">
        <div class="flower-burst" id="flowerBurst"></div>
    </div>

    <div class="container mt-5">
        <h1 class="text-center text-light fw-bold">YABATECH INTERFALCULTY QUIZ</h1>
        <!-- Confetti Canvas -->
        <canvas id="confettiCanvas"></canvas>

        <!-- Audio for Correct Answer -->
        <audio id="correctSound" src="{{ asset('success.wav') }}"></audio>
        <audio id="failedSound" src="{{ asset('fail.wav') }}"></audio>

        <div class="row mt-4">
            <!-- Sidebar - Department Scores -->
            <div class="col-md-4">
                <div class="card shadow-lg p-3">
                    <h4 class="text-primary text-center">Department Scores</h4>
                    <ul class="list-group">
                        @foreach ($departments as $department)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>{{ $department->name }}</strong>
                                <span class="badge bg-success" id="point{{ $department->id }}">
                                    {{ $department->total_points }}
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Next Round Button -->
                    <div class="text-center mt-3">
                        <a href="/quiz/{{ $round + 1 }}">
                            <button class="btn btn-primary mt-2" id="nextRound">Next Round</button>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Questions Section -->
            <div class="col-md-8">
                <div class="card shadow-lg p-3">
                    <h4 class="text-center text-light"><b>Current Department:
                            <span id="currentDepartment">
                                {{ $currentDepartment ? $currentDepartment->name : 'N/A' }}
                            </span></b>
                    </h4>

                    <div id="questionsContainer" class="row g-3 mt-3">
                        @foreach ($questions as $question)
                            <div class="col-3 text-center">
                                <button type="button" class="ball" id="questionButton{{ $question->id }}"
                                    onclick="fetchQuestion({{ $question->id }})"
                                    @if (in_array($question->id, $answeredQuestions)) disabled @endif>
                                    {{ in_array($question->id, $answeredQuestions) ? '✔' : $loop->iteration }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Question Modal -->
    <div id="questionModal" class="modal fade" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="questionText"></h5>
                </div>
                <div class="modal-body">
                    <form id="answerForm">
                        <div id="optionsContainer" class="mb-3"></div>
                        <input type="hidden" id="departmentId" name="department_id">
                        <span id="quesionTimer" class="text-danger fw-bold"></span>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success fw-bold">Submit Answer</button>
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

        // 🎉 Confetti Canvas Setup
        const canvas = document.getElementById("confettiCanvas");
        const ctx = canvas.getContext("2d");

        // Resize Canvas
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        // Confetti Particles
        let confettiParticles = [];
        const colors = ["#FF0", "#F00", "#0F0", "#00F", "#FF4500", "#FFD700", "#00FA9A"];

        function createConfetti() {
            for (let i = 0; i < 200; i++) {
                confettiParticles.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height - canvas.height,
                    r: Math.random() * 10 + 4,
                    d: Math.random() * 5 + 2,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    tilt: Math.random() * 5,
                    speed: Math.random() * 3 + 2
                });
            }
        }

        // 🎆 Animate Confetti
        function drawConfetti() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            confettiParticles.forEach((p, index) => {
                ctx.beginPath();
                ctx.fillStyle = p.color;
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2, false);
                ctx.fill();
                p.y += p.speed;
                p.x += Math.sin(p.tilt) * 2;

                if (p.y > canvas.height) confettiParticles.splice(index, 1);
            });

            requestAnimationFrame(drawConfetti);
        }

        // 🌟 Trigger Celebration
        function startCelebration() {
            createConfetti();
            drawConfetti();

            // Play Celebration Sound
            document.getElementById("correctSound").play();

            // Stop after 3 seconds
            setTimeout(() => {
                confettiParticles = [];
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }, 7500);
        }


        function fetchQuestion(questionId) {
            const currentDepartment = departments[currentDepartmentIndex];
            const departmentId = currentDepartment.id;

            $.get(`/quiz/question/${questionId}`, function(data) {
                $('#questionText').text(data.question.question);
                const options = JSON.parse(data.question.options);
                $('#optionsContainer').empty();

                const optionLabels = ['A', 'B', 'C', 'D']; // Labels for options

                options.forEach((option, index) => {
                    $('#optionsContainer').append(`
            <div class="form-check">
                <input type="radio" class="form-check-input" id="option${index}" name="answer" value="${option}" required>
                <label class="form-check-label" for="option${index}">
                    <strong>${optionLabels[index]}.</strong> ${option}
                </label>
            </div>`);
                });

                $('#departmentId').val(departmentId);
                $('#answerForm').data('questionId', questionId);
                startTimer(10);
                $('#questionModal').modal('show');
            });

        }

        let timerInterval;

        function startTimer(duration) {
            let timer = duration;
            clearInterval(timerInterval);
            timerInterval = setInterval(function() {
                $('#quesionTimer').text(`${timer}s`);
                if (--timer < 0) {
                    clearInterval(timerInterval);
                    $('#quesionTimer').text("Time's up!");
                    autoSubmitAnswer();
                }
            }, 1000);
        }

        function autoSubmitAnswer() {
            const questionId = $('#answerForm').data('questionId');
            const departmentId = $('#departmentId').val();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.post('/quiz/submit-answer', {
                question_id: questionId,
                department_id: departmentId,
                is_answered: false,
                round: {{ $round }},
            }, function(data) {
                Swal.fire({
                    icon: 'error',
                    title: 'Time is up!',
                    text: 'You missed this question.'
                });
                document.getElementById("failedSound").play();

                moveToNextDepartment(questionId);
            });
        }

        $('#answerForm').submit(function(e) {
            e.preventDefault();
            clearInterval(timerInterval);

            const questionId = $(this).data('questionId');
            const departmentId = $('#departmentId').val();
            const selectedAnswer = $('input[name="answer"]:checked').val();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.post('/quiz/submit-answer', {
                question_id: questionId,
                department_id: departmentId,
                selected_answer: selectedAnswer,
                round: {{ $round }},
            }, function(data) {
                if (data.nextround) {
                    $("#nextRound").show();
                }


                if (data.is_correct) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Correct!',
                        text: 'Good job!'
                    });

                    // 🎉 Trigger Confetti & Sound
                    startCelebration();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Wrong!',
                        text: 'Better luck next time.'
                    });
                    document.getElementById("failedSound").play();
                }

                updateScore(departmentId, data.points_awarded);
                moveToNextDepartment(questionId);
            });
        });

        function moveToNextDepartment(questionId) {
            $(`#questionButton${questionId}`).addClass('btn-success').attr('disabled', true).text('✔');
            currentDepartmentIndex = (currentDepartmentIndex + 1) % departments.length;
            $('#currentDepartment').text(departments[currentDepartmentIndex].name);
            $('#questionModal').modal('hide');
        }

        function updateScore(departmentId, points) {
            const scoreElement = $(`#point${departmentId}`);
            let currentScore = parseInt(scoreElement.text());
            scoreElement.text(currentScore + points);
        }
    </script>
@endsection
