<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href=".././assets/style.css">
</head>

<body>
<!-- Side Bar -->
<div class="fixed top-0 left-0 w-[230px] h-[100%] z-50 overflow-hidden sidebar">
        <a href="" class="logo text-xl font-bold h-[56px] flex items-center text-[#1976D2] z-30 pb-[20px] box-content">
            <i class="mt-4 text-xxl max-w-[60px] flex justify-center"><i class="fa-solid fa-car-side"></i></i>
            <div class="logoname ml-2"><span>Loca</span>Auto</div>
        </a>
        <ul class="side-menu w-full mt-12">
            <li class="h-12 bg-transparent ml-2.5 rounded-l-full p-1"><a href="listClients.php" class="menu-item"><i class="fa-solid fa-user-group"></i>Clients</a></li>
            <li class="h-12 bg-transparent ml-2.5 rounded-l-full p-1"><a href="listCars.php" class="menu-item"><i class="fa-solid fa-car"></i>Cars</a></li>
            <li class="active h-12 bg-transparent ml-1.5 rounded-l-full p-1"><a href="categories.php" class="menu-item"><i class="fa-solid fa-file-contract"></i>Categories</a></li>
            <li class="active h-12 bg-transparent ml-1.5 rounded-l-full p-1"><a href="listReservation.php" class="menu-item"><i class="fa-solid fa-calendar-check"></i>Reservations</a></li>
            <li class="h-12 bg-transparent ml-2.5 rounded-l-full p-1">
            <a href="listThemes.php" class="menu-item"><i class="fa-solid fa-layer-group"></i>Themes</a>
        </li>
        <li class="h-12 bg-transparent ml-2.5 rounded-l-full p-1">
            <a href="listArticle.php" class="menu-item"><i class="fa-solid fa-newspaper"></i>Articles</a>
        </li>
        <li class="h-12 bg-transparent ml-1.5 rounded-l-full p-1">
            <a href="listTags.php" class="menu-item"><i class="fa-solid fa-tags"></i>Tags</a>
        </li>
        <li class="active h-12 bg-transparent ml-1.5 rounded-l-full p-1">
            <a href="listCommentaires.php" class="menu-item"><i class="fa-solid fa-comments"></i>Comments</a>
        </li>
             <ul class="side-menu w-full mt-12">
            <li class="h-12 bg-transparent ml-2.5 rounded-l-full p-1">
            <a href=".././controllers/logout.php" class="logout">
                    <i class='bx bx-log-out-circle'></i> Logout
                </a>
            </li>
        </ul>
    </div>
<!-- Content -->
<div class="content">
    <!-- Navbar -->
    <nav class="flex items-center gap-6 h-14 bg-[#f6f6f9] sticky top-0 left-0 z-50 px-6">
        <i class='bx bx-menu'></i>
        <form action="#" class="max-w-[400px] w-full mr-auto">
            <div class="form-input flex items-center h-[36px]">
                <input class="flex-grow px-[16px] h-full border-0 bg-[#eee] rounded-l-[36px] outline-none w-full text-[#363949]" type="search" placeholder="Search comments...">
                <button class="w-[80px] h-full flex justify-center items-center bg-[#1976D2] text-[#f6f6f9] text-[18px] border-0 outline-none rounded-r-[36px] cursor-pointer" type="submit">
                    <i class='bx bx-search'></i>
                </button>
            </div>
        </form>
        <a href="#" class="profile">
            <img class="w-[36px] h-[36px] object-cover rounded-full" src=".././assets/image/admin-profile.png" alt="Profile">
        </a>
    </nav>

    <!-- Main Content -->
    <main class="mainn w-full p-[36px_24px] max-h-[calc(100vh_-_56px)]">
        <div class="header flex items-center justify-between gap-[16px] flex-wrap">
            <h1 class="text-2xl font-bold">Comments Management</h1>
            <div class="flex gap-3">
                <select class="h-[36px] px-[16px] rounded-[36px] border border-gray-300">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="spam">Spam</option>
                </select>
            </div>
        </div>

        <!-- Comments Overview -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 bg-blue-50 rounded-lg">
                <h3 class="text-lg font-semibold text-blue-800">Total</h3>
                <p class="text-2xl font-bold text-blue-900">156</p>
            </div>
            <div class="p-4 bg-green-50 rounded-lg">
                <h3 class="text-lg font-semibold text-green-800">Approved</h3>
                <p class="text-2xl font-bold text-green-900">124</p>
            </div>
            <div class="p-4 bg-yellow-50 rounded-lg">
                <h3 class="text-lg font-semibold text-yellow-800">Pending</h3>
                <p class="text-2xl font-bold text-yellow-900">32</p>
            </div>
        </div>

        <!-- Table -->
        <div class="mt-8">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Author</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Comment</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Article</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Date</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Status</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="py-3 px-3 text-sm border-b border-grey">
                            <div class="flex items-center gap-2">
                                <img src="/api/placeholder/32/32" alt="User" class="w-8 h-8 rounded-full">
                                <div>
                                    <p class="font-medium">John Smith</p>
                                    <p class="text-xs text-gray-500">john@email.com</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-3 text-sm border-b border-grey">Great article! I particularly love...</td>
                        <td class="py-3 px-3 text-sm border-b border-grey">New Tesla Models 2024</td>
                        <td class="py-3 px-3 text-sm border-b border-grey">01/07/2024</td>
                        <td class="py-3 px-3 text-sm border-b border-grey">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Approved</span>
                        </td>
                        <td class="py-3 px-3 text-sm border-b border-grey">
                            <div class="flex gap-2">
                                <button class="text-blue-500 hover:text-blue-700" title="View">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <button class="text-green-500 hover:text-green-700" title="Approve">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                                <button class="text-red-500 hover:text-red-700" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="mt-4 flex justify-between items-center">
                <p class="text-sm text-gray-600">Showing 1-10 of 156 comments</p>
                <div class="flex gap-2">
                    <button class="px-3 py-1 border rounded-lg hover:bg-gray-50">Previous</button>
                    <button class="px-3 py-1 bg-blue-500 text-white rounded-lg">1</button>
                    <button class="px-3 py-1 border rounded-lg hover:bg-gray-50">2</button>
                    <button class="px-3 py-1 border rounded-lg hover:bg-gray-50">3</button>
                    <button class="px-3 py-1 border rounded-lg hover:bg-gray-50">Next</button>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- View Comment Modal -->
<div id="viewCommentModal" class="fixed right-[-100%] w-full max-w-[500px] h-[500px] shadow-[2px_0_10px_rgba(0,0,0,0.1)] p-6 flex flex-col gap-5 transition-all duration-700 ease-in-out z-50 top-[166px] bg-white">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-semibold">Comment Details</h2>
        <button id="closeViewModal" class="text-gray-500 hover:text-gray-700">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="flex flex-col gap-4">
        <div>
            <h3 class="font-medium">Author</h3>
            <p>John Smith (john@email.com)</p>
        </div>
        <div>
            <h3 class="font-medium">Article</h3>
            <p>New Tesla Models 2024</p>
        </div>
        <div>
            <h3 class="font-medium">Comment</h3>
            <p class="mt-2 text-gray-700">Great article! I particularly love the detailed analysis of the new features. Keep up the great work!</p>
        </div>
        <div>
            <h3 class="font-medium">Metadata</h3>
            <p class="text-sm text-gray-600">IP: 192.168.1.1</p>
            <p class="text-sm text-gray-600">Browser: Chrome 120.0</p>
            <p class="text-sm text-gray-600">Date: 01/07/2024 15:30</p>
        </div>
    </div>
    <div class="mt-auto flex gap-2">
        <button class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">Approve</button>
        <button class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">Mark as Spam</button>
        <button class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Delete</button>
    </div>
</div>

<script src=".././assets/main.js"></script>
</body>
</html>