<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <!-- Logo Container -->
            <div class="flex justify-center mb-8">
                <div class="w-24 h-24 flex items-center justify-center">
                    <img src="assets/images/logo.svg" alt="Veils India Logo" class="w-full h-full text-blue-600">
                </div>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">Welcome to Veils India</h2>
            <p class="mt-2 text-sm text-gray-600">Sign in to access your dashboard</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <span class="material-icons text-red-500">error_outline</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            Invalid username or password. Please try again.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="../backend/login.php" method="POST">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="username" class="sr-only">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-gray-400 text-xl">person_outline</span>
                        </div>
                        <input id="username" name="username" type="text" required 
                               class="appearance-none rounded-none relative block w-full px-3 py-3 pl-12
                                      border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md
                                      focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="Username">
                    </div>
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-icons text-gray-400 text-xl">lock_outline</span>
                        </div>
                        <input id="password" name="password" type="password" required
                               class="appearance-none rounded-none relative block w-full px-3 py-3 pl-12
                                      border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md
                                      focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="Password">
                    </div>
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent 
                               text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 
                               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500
                               transition-colors duration-200">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <span class="material-icons text-blue-500 group-hover:text-blue-400">login</span>
                    </span>
                    Sign in
                </button>
            </div>
        </form>
            <div class="mt-8 space-y-4">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-gray-50 text-gray-500">Demo Accounts</span>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg border border-gray-200 divide-y divide-gray-200">
                    <div class="p-4">
                        <div class="flex items-center space-x-3">
                            <span class="material-icons text-purple-500">admin_panel_settings</span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Super Admin</p>
                                <p class="text-xs text-gray-500">superadmin / SuperAdmin123!</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center space-x-3">
                            <span class="material-icons text-blue-500">manage_accounts</span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Admin</p>
                                <p class="text-xs text-gray-500">adminuser / AdminUser123!</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center space-x-3">
                            <span class="material-icons text-green-500">person</span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Normal User</p>
                                <p class="text-xs text-gray-500">normaluser / NormalUser123!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 text-center">
                <p class="text-xs text-gray-500">
                    © 2025 Veils India Private Limited. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
