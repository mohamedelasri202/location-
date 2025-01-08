<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
        }
        
        .blog-card {
            transition: transform 0.3s ease;
        }
        
        .blog-card:hover {
            transform: translateY(-5px);
        }
        
        .tag {
            transition: all 0.3s ease;
        }
        
        .tag:hover {
            background-color: #2563eb;
            color: white;
        }
        
        .add-article-btn {
            transition: all 0.3s ease;
        }
        
        .add-article-btn:hover {
            transform: scale(1.05);
        }
        
        .form-overlay {
            backdrop-filter: blur(5px);
        }
    </style>
</head>
<body>
    <!-- Blog Page Section -->
    <div id="blogPage" class="page">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">Blog Articles</h2>
                <button class="add-article-btn bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg flex items-center gap-2 shadow-md">
                    <i class="fas fa-plus"></i>
                    Add Article
                </button>
            </div>

            <!-- Article Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Article Card Template -->
                <div class="blog-card bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Article Title</h3>
                        <div class="flex gap-2 mb-4">
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                                Theme
                            </span>
                        </div>
                        <div class="flex gap-2 mb-4 flex-wrap">
                            <span class="tag bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-sm cursor-pointer">
                                #tag1
                            </span>
                            <span class="tag bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-sm cursor-pointer">
                                #tag2
                            </span>
                        </div>
                        <p class="text-gray-600 mb-4 line-clamp-3">
                            Article content preview goes here...
                        </p>
                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-user"></i>
                                Author Name
                            </span>
                            <span class="flex items-center gap-2">
                                <i class="fas fa-calendar"></i>
                                Date
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Article Form Modal -->
            <div class="fixed inset-0 bg-black bg-opacity-50 form-overlay hidden">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-2xl">
                        <h3 class="text-2xl font-bold mb-6">Add New Article</h3>
                        <form class="space-y-4">
                            <div>
                                <input type="text" placeholder="Article Title" 
                                       class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            </div>
                            <div>
                                <select class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                    <option value="">Select Theme</option>
                                </select>
                            </div>
                            <div>
                                <select multiple class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none h-32">
                                    <option value="">Select Tags</option>
                                </select>
                            </div>
                            <div>
                                <textarea placeholder="Article Content" rows="6" 
                                          class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none"></textarea>
                            </div>
                            <div class="flex gap-4">
                                <button type="submit" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg flex-1">
                                    Publish Article
                                </button>
                                <button type="button" 
                                        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>