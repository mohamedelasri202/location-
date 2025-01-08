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
                <input class="flex-grow px-[16px] h-full border-0 bg-[#eee] rounded-l-[36px] outline-none w-full text-[#363949]" type="search" placeholder="Search articles...">
                <button class="w-[80px] h-full flex justify-center items-center bg-[#1976D2] text-[#f6f6f9] text-[18px] border-0 outline-none rounded-r-[36px] cursor-pointer" type="submit">
                    <i class='bx bx-search'></i>
                </button>
            </div>
        </form>
        <div class="flex items-center gap-4">
            <select class="p-2 rounded-lg border border-gray-300">
                <option value="5">5 per page</option>
                <option value="10">10 per page</option>
                <option value="15">15 per page</option>
            </select>
            <a href="#" class="profile">
                <img class="w-[36px] h-[36px] object-cover rounded-full" src=".././assets/image/admin-profile.png" alt="Profile">
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="mainn w-full p-[36px_24px] max-h-[calc(100vh_-_56px)]">
        <div class="header flex items-center justify-between gap-[16px] flex-wrap">
            <h1 class="text-2xl font-bold">Article Management</h1>
            <div class="flex gap-4">
                <select class="p-2 rounded-lg border border-gray-300">
                    <option value="">Filter by Theme</option>
                    <option value="luxury">Luxury Cars</option>
                    <option value="electric">Electric Vehicles</option>
                </select>
                <a id="buttonadd" href="#" class="report h-[36px] px-[16px] rounded-[36px] bg-[#1976D2] text-[#f6f6f9] flex items-center justify-center gap-[10px] font-medium">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add Article</span>
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="mt-8">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Title</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Author</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Theme</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Status</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Date</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="py-3 px-3 text-sm border-b border-grey">The Future of Electric Cars</td>
                        <td class="py-3 px-3 text-sm border-b border-grey">John Doe</td>
                        <td class="py-3 px-3 text-sm border-b border-grey">Electric Vehicles</td>
                        <td class="py-3 px-3 text-sm border-b border-grey">
                            <span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                        </td>
                        <td class="py-3 px-3 text-sm border-b border-grey">2024-01-05</td>
                        <td class="py-3 px-3 text-sm border-b border-grey">
                            <div class="flex gap-2">
                                <button class="text-green-500 hover:text-green-700">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                                <button class="text-blue-500 hover:text-blue-700">
                                    <i class="fa-solid fa-edit"></i>
                                </button>
                                <button class="text-red-500 hover:text-red-700">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="flex justify-center gap-2 mt-4">
                <button class="px-3 py-1 rounded-lg border border-gray-300">Previous</button>
                <button class="px-3 py-1 rounded-lg bg-blue-500 text-white">1</button>
                <button class="px-3 py-1 rounded-lg border border-gray-300">2</button>
                <button class="px-3 py-1 rounded-lg border border-gray-300">3</button>
                <button class="px-3 py-1 rounded-lg border border-gray-300">Next</button>
            </div>
        </div>
    </main>
</div>

<!-- Add Article Modal -->
<div id="addArticleForm" class="add-client-form fixed right-[-100%] w-full max-w-[600px] h-[600px] shadow-[2px_0_10px_rgba(0,0,0,0.1)] p-6 flex flex-col gap-5 transition-all duration-700 ease-in-out z-50 top-[166px] bg-white">
    <form action="#" method="post" class="flex flex-col gap-4">
        <h2 class="text-2xl font-semibold mb-5">Add New Article</h2>
        <div class="form-group flex flex-col">
            <label for="articleTitle" class="text-sm text-gray-700 mb-1">Title</label>
            <input name="articleTitle" type="text" id="articleTitle" placeholder="Enter article title" class="p-2 border border-gray-300 rounded-lg outline-none text-sm">
        </div>
        <div class="form-group flex flex-col">
            <label for="articleContent" class="text-sm text-gray-700 mb-1">Content</label>
            <textarea name="articleContent" id="articleContent" placeholder="Enter article content" class="p-2 border border-gray-300 rounded-lg outline-none text-sm" rows="6"></textarea>
        </div>
        <div class="form-group flex flex-col">
            <label for="articleTheme" class="text-sm text-gray-700 mb-1">Theme</label>
            <select name="articleTheme" id="articleTheme" class="p-2 border border-gray-300 rounded-lg outline-none text-sm">
                <option value="">Select Theme</option>
                <option value="luxury">Luxury Cars</option>
                <option value="electric">Electric Vehicles</option>
            </select>
        </div>
        <div class="form-group flex flex-col">
            <label for="articleTags" class="text-sm text-gray-700 mb-1">Tags</label>
            <input name="articleTags" type="text" id="articleTags" placeholder="Enter tags (comma separated)" class="p-2 border border-gray-300 rounded-lg outline-none text-sm">
        </div>
        <div class="form-group flex flex-col">
            <label for="articleImage" class="text-sm text-gray-700 mb-1">Image (Optional)</label>
            <input type="file" name="articleImage" id="articleImage" class="p-2 border border-gray-300 rounded-lg outline-none text-sm">
        </div>
        <button type="submit" class="submit-btn border-none px-4 py-2 rounded-lg cursor-pointer transition-all duration-500 ease-in-out bg-[#1976D2] text-white">Add Article</button>
        <button type="button" id="closeForm" class="close-btn border-none px-4 py-2 rounded-lg cursor-pointer transition-all duration-500 ease-in-out bg-gray-300">Close</button>
    </form>
</div>

<script src=".././assets/main.js"></script>
</body>
</html>