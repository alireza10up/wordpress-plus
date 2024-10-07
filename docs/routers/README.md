📄 Documentation for 🚐 Router Class

📝 Overview

The 🚐 Router class is a 🛠️ utility for handling custom 🛣️ routing logic in 🌀 WordPress, providing features similar to those of traditional 🌐 web frameworks. It can manage 🛤️ routes for 📄 post types, resources, and more, and dispatch 📨 requests to 🎮 controllers.

📚 Methods

1. 📄 postType(string $postTypeName, string $controller)

📝 Registers a post type with a specific controller.

📌 Parameters:

$postTypeName (📝 string): The name of the 📄 post type.

$controller (📝 string): The 🎮 controller class handling the post type.

🛠️ Example Usage :

2. 🗄️ resourceAdmin(string $resourceName, string $controller)

📝 Registers an admin resource, creating a custom 📜 menu and 🖋️ CRUD actions in the 🌀 WordPress admin panel.

📌 Parameters:

$resourceName (📝 string): The name of the resource.

$controller (📝 string): The 🎮 controller class handling the resource.

🛠️ Example Usage :

3. ➡️ get(string $route, string $controller, string $method)

📝 Registers a GET 🛣️ route, specifying a 🎮 controller and a method to handle the route.

📌 Parameters:

$route (📝 string): The 🛤️ route pattern (e.g., '🛒 product/{id}').

$controller (📝 string): The 🎮 controller class handling the route.

$method (📝 string): The method in the 🎮 controller to be called when this route is accessed.

🛠️ Example Usage :

⏳ Soon Available

🛠️ Support for other HTTP verbs:

📤 POST

📝 PUT

🗑️ DELETE

⚙️ AJAX handling.

⚙️ Initialization

Make sure to call 🚐 Router::init() after defining all your 🛤️ routes. This will properly initialize the 🛣️ routing logic and register the necessary ⚓ hooks.