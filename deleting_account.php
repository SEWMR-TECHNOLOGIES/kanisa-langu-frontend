<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Account Deletion Request</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .focus\:ring-brand-500:focus { --tw-ring-color: #D65747; }
        .card-border-gradient {
            border: 1px solid transparent;
            background: 
                linear-gradient(to right, white, white), 
                linear-gradient(to bottom right, #FEE2E2, #FECACA, #F9FAFB);
            background-clip: padding-box, border-box;
            background-origin: padding-box, border-box;
        }
        /* Simple fade-in animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        /* Hide element */
        .hidden { display: none; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Main Container for the Form -->
        <div id="form-container">
            <div class="bg-white w-full p-8 sm:p-10 rounded-2xl shadow-xl card-border-gradient">
                <div class="flex items-start space-x-4 mb-6">
                    <div class="flex-shrink-0 h-12 w-12 flex items-center justify-center bg-red-50 rounded-full">
                        <svg class="w-7 h-7 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.134-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.067-2.09 1.02-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Delete Account</h2>
                        <p class="text-sm text-gray-500 mt-1">Request the permanent deletion of your account.</p>
                    </div>
                </div>

                <!-- Error Message Display Area (initially hidden) -->
                <div id="error-message" class="hidden bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded-md mb-6 text-sm" role="alert">
                    <p><span class="font-bold">Oops!</span> <span id="error-text"></span></p>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-200">
                    <h3 class="font-semibold text-gray-800 text-sm mb-2">Please Read Carefully</h3>
                    <p class="text-xs text-gray-600">This action is irreversible. All associated data will be permanently removed.</p>
                </div>

                <form id="deletion-form" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Your Account Email <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zm0 0c0 1.657 1.007 3 2.25 3S21 13.657 21 12a9 9 0 10-2.636 6.364M16.5 12V8.25" /></svg>
                            </div>
                            <input type="email" name="email" id="email" placeholder="you@example.com" class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg shadow-sm transition duration-150 ease-in-out focus:ring-2 focus:ring-brand-500 focus:border-transparent focus:outline-none" />
                        </div>
                    </div>

                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for Leaving <span class="text-gray-400">(Optional)</span></label>
                        <textarea name="reason" id="reason" rows="4" placeholder="We'd appreciate your feedback to help us improve..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm transition duration-150 ease-in-out focus:ring-2 focus:ring-brand-500 focus:border-transparent focus:outline-none resize-none"></textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" id="submit-button" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 ease-in-out transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-md hover:shadow-lg disabled:bg-red-400 disabled:cursor-not-allowed disabled:scale-100">
                            Submit Deletion Request
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Success State UI (initially hidden) -->
        <div id="success-container" class="hidden bg-white p-8 sm:p-10 rounded-2xl shadow-xl text-center animate-fade-in">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-5">
                <svg class="h-8 w-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Request Submitted</h1>
            <p class="text-gray-600 text-base">Thank you. Your request has been submitted and will be processed within 48 hours.</p>
            <p class="text-gray-500 text-sm mt-4">You will receive an email confirmation once the deletion is complete.</p>
        </div>

        <p class="text-xs text-gray-500 text-center mt-8">&copy; <?php echo date('Y'); ?> SEWMR Technologies</p>
    </div>

    <script>
        // --- Client-Side Logic for API communication ---
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('deletion-form');
            const submitButton = document.getElementById('submit-button');
            const errorMessageContainer = document.getElementById('error-message');
            const errorTextElement = document.getElementById('error-text');
            const formContainer = document.getElementById('form-container');
            const successContainer = document.getElementById('success-container');

            form.addEventListener('submit', async (event) => {
                event.preventDefault(); // Prevent traditional form submission

                // --- UI State: Loading ---
                submitButton.disabled = true;
                submitButton.textContent = 'Submitting...';
                errorMessageContainer.classList.add('hidden');

                const formData = new FormData(form);
                const data = {
                    email: formData.get('email'),
                    reason: formData.get('reason')
                };

                try {
                    // --- API Call ---
                    const response = await fetch('/api/deleting_account.php', { 
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        // --- UI State: Success ---
                        formContainer.classList.add('hidden');
                        successContainer.classList.remove('hidden');
                    } else {
                        // --- UI State: Error ---
                        throw new Error(result.message || 'An unknown error occurred.');
                    }

                } catch (error) {
                    // --- UI State: Error ---
                    errorTextElement.textContent = error.message;
                    errorMessageContainer.classList.remove('hidden');
                    
                    // Reset button
                    submitButton.disabled = false;
                    submitButton.textContent = 'Submit Deletion Request';
                }
            });
        });
    </script>

</body>
</html>
