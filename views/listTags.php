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
                <input class="flex-grow px-[16px] h-full border-0 bg-[#eee] rounded-l-[36px] outline-none w-full text-[#363949]" type="search" placeholder="Search tags...">
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
            <h1 class="text-2xl font-bold">Tag Management</h1>
            <a id="buttonadd" href="#" class="report h-[36px] px-[16px] rounded-[36px] bg-[#1976D2] text-[#f6f6f9] flex items-center justify-center gap-[10px] font-medium">
                <i class="fa-solid fa-plus"></i>
                <span>Add Tags</span>
            </a>
        </div>

        <!-- Tag Cloud Preview -->
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <h2 class="text-lg font-semibold mb-3">Tag Cloud</h2>
            <div class="flex flex-wrap gap-2">
                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">#luxury (25)</span>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">#electric (18)</span>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">#sports (15)</span>
                <!-- Add more tag previews -->
            </div>
        </div>

        <!-- Table -->
        <div class="mt-8">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Tag Name</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Usage Count</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Created Date</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Last Used</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="py-3 px-3 text-sm border-b border-grey">#luxury</td>
                        <td class="py-3 px-3 text-sm border-b border-grey">25</td>
                        <td class="py-3 px-3 text-sm border-b border-grey">2024-01-01</td>
                        <td class="py-3 px-3 text-sm border-b border-grey">2024-01-07</td>
                        <td class="py-3 px-3 text-sm border-b border-grey">
                            <div class="flex gap-2">
                                <button class="text-blue-500 hover:text-blue-700">
                                    <i class="fa-solid fa-edit"></i>
                                </button>
                                <button class="text-blue-500 hover:text-blue-700">
                                    <i class="fa-solid fa-link"></i>
                                </button>
                                <button class="text-red-500 hover:text-red-700">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Add Tags Modal -->
<div id="addTagForm" class="add-client-form fixed right-[-100%] w-full max-w-[400px] h-[400px] shadow-[2px_0_10px_rgba(0,0,0,0.1)] p-6 flex flex-col gap-5 transition-all duration-700 ease-in-out z-50 top-[166px] bg-white">
    <form action="#" method="post" class="flex flex-col gap-4">
        <h2 class="text-2xl font-semibold mb-5">Add Multiple Tags</h2>
        <div class="form-group flex flex-col">
            <label for="tagNames" class="text-sm text-gray-700 mb-1">Tag Names</label>
            <textarea name="tagNames" id="tagNames" placeholder="Enter multiple tags (one per line)" class="p-2 border border-gray-300 rounded-lg outline-none text-sm" rows="4"></textarea>
            <p class="text-xs text-gray-500 mt-1">Enter each tag on a new line</p>
        </div>
        <div class="form-group flex flex-col">
            <label class="text-sm text-gray-700 mb-1">Category</label>
            <select class="p-2 border border-gray-300 rounded-lg outline-none text-sm">
                <option value="">Select Category</option>
                <option value="vehicle-type">Vehicle Type</option>
                <option value="features">Features</option>
                <option value="brands">Brands</option>
            </select>
        </div>
        <button type="submit" class="submit-btn border-none px-4 py-2 rounded-lg cursor-pointer transition-all duration-500 ease-in-out bg-[#1976D2] text-white">Add Tags</button>
        <button type="button" id="closeForm" class="close-btn border-none px-4 py-2 rounded-lg cursor-pointer transition-all duration-500 ease-in-out bg-gray-300">Close</button>
    </form>
</div>

<script src=".././assets/main.js"></script>
</body>
</html>