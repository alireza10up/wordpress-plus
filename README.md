# **WordPress Plus Framework**

A Laravel-inspired micro-framework for developing WordPress plugins, providing a structured MVC approach with routing, custom post types, and ORM integration. This framework simplifies WordPress plugin development while adhering to modern PHP standards.

## **Requirements**

- **PHP version:** 8.2+
- **WordPress version:** 5.0+
- **Composer**

---

## **Installation**

1. Install the framework via Composer by running:

   ```bash
   composer require alireza10up/wordpress-plus
   ```

2. Include the autoload file in your plugin’s main file:

   ```php
   require_once __DIR__ . '/vendor/autoload.php';
   ```

---

## **Basic Usage**

### **1. Register Custom Post Types**

The framework allows you to register custom post types and automatically link them to a controller for handling CRUD operations. For example, to register a `Product` post type and link it to a `ProductController`:

```php
use Alireza10up\WordpressPlus\Routing\Router;
use App\Controllers\ProductController;

add_action('init', function() {
    Router::handlePostTypeWithController('product', ProductController::class);
});
```

This will automatically create the `product` post type, set up admin menu entries, and manage CRUD operations via the `ProductController`.

### **2. Creating a Controller**

Controllers in the framework manage requests for custom post types. You can extend the base `BaseController` class to create your own controller. Each controller can handle methods like `index`, `create`, `store`, `edit`, `update`, and `delete`.

```php
namespace App\Controllers;

use Alireza10up\WordpressPlus\Http\BaseController;

class ProductController extends BaseController
{
    public function index()
    {
        $products = get_posts(['post_type' => 'product']);
        $this->view('product.list', compact('products'));
    }

    public function create()
    {
        $this->view('product.create', []);
    }

    public function store()
    {
        // Code to handle storing a new product
        wp_insert_post([
            'post_type' => 'product',
            'post_title' => sanitize_text_field($_POST['title']),
            'post_status' => 'publish',
        ]);
        wp_redirect(admin_url('edit.php?post_type=product'));
        exit;
    }
}
```

### **3. Working with Views**

You can render views from the controller using the `view` method from `BaseController`. The view files should be stored in the appropriate directory.

Example:

```php
$this->view('product.list', ['products' => $products]);
```

In your `Views` directory, create a file `product/list.php` to display the data:

```php
<h1>Product List</h1>
<?php foreach ($products as $product): ?>
    <div>
        <h2><?php echo $product->post_title; ?></h2>
        <a href="<?php echo get_edit_post_link($product->ID); ?>">Edit</a>
    </div>
<?php endforeach; ?>
```

### **4. Handling JSON Responses**

The `BaseController` provides helper methods for sending JSON responses:

- **Success Response**:

    ```php
    $this->jsonSuccessResponse('Data saved successfully', 200);
    ```

- **Error Response**:

    ```php
    $this->jsonErrorResponse('Error saving data', 400);
    ```

### **5. Models**

Models are created by extending the `BaseModel`, which uses the **Dbout ORM**. Here’s an example of a simple model:

```php
namespace App\Models;

use Alireza10up\WordpressPlus\Database\BaseModel;

class Product extends BaseModel
{
    protected $table = 'products';
}
```

---

## **Error Handling**

The framework throws a custom `HandlerNotExistException` if a method (e.g., `index`, `store`, `edit`) does not exist in the associated controller for a custom post type.

---

## **Folder Structure**

```bash
my-plugin/
├── src/
│   ├── Controllers/
│   │   └── ProductController.php
│   ├── Models/
│   │   └── Product.php
│   ├── Views/
│   │   └── product/
│   │       └── list.php
│   └── main-file-plugin.php
├── vendor/
├── composer.json
```

---

## **License**

This project is licensed under the MIT License - see the LICENSE file for details.

---

By following this structure and example, you can easily manage custom post types, routing, and views in WordPress with modern PHP practices!