<?php
session_start();


$host = 'localhost';
$dbname = 'greenhorizon_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_description'])) 
{
    
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}

    $userId = $_SESSION['user_id'];
    $postDescription = htmlspecialchars($_POST['post_description'], ENT_QUOTES, 'UTF-8');
    $uploadDir = 'uploads/';
    $uploadFile = '';

    
    if (!empty($_FILES['post_image']['name'])) {
        $fileName = basename($_FILES['post_image']['name']);
        $uploadFile = $uploadDir . uniqid() . '_' . $fileName;

        if (move_uploaded_file($_FILES['post_image']['tmp_name'], $uploadFile)) {
            $uploadFile = htmlspecialchars($uploadFile, ENT_QUOTES, 'UTF-8');
        } else {
            echo "Error uploading the image.";
            $uploadFile = '';
        }
    }

    
    try {
        $query = "INSERT INTO posts (user_id, description, image_path) VALUES (:user_id, :description, :image_path)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':description', $postDescription, PDO::PARAM_STR);
        $stmt->bindParam(':image_path', $uploadFile, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $message = "Post submitted successfully!";
        } else {
            $message = "Failed to submit the post.";
        }
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
    }
}
$posts = [];
$stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
if ($stmt) {
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

foreach ($posts as &$post) {
    $postId = $post['id'];
    $commentStmt = $pdo->prepare("
        SELECT comments.*, students.full_name, 
               (SELECT COUNT(*) FROM reported_comments 
                WHERE reported_comments.comment_id = comments.id 
                AND reported_comments.reported_by = :user_id) AS is_reported
        FROM comments 
        JOIN students ON comments.user_email = students.email 
        WHERE comments.post_id = :post_id 
        ORDER BY comments.created_at DESC
    ");
    $commentStmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
    $userId = $_SESSION['user_id']; 
    $commentStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $commentStmt->execute();
    $post['comments'] = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="student_dashboard_style.css?v=1.1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        




    </style>
</head>
<body>
<header class="navbar">
    <div class="brand">
        <h1>GreenHorizon</h1>
    </div>

    
    
    <div class="nav-icons">
        
        <a href="student_dashboard.php" class="home-icon">
        <i class="fa-solid fa-house fa-xl" style="color: #228b22;"></i>
        </a>
        <a href="about2.php" class="home-icon">
        <i class="fa-solid fa-circle-info fa-xl" style="color: #228b22;"></i>
</a>
        
        <div class="dropdown">
            <button class="account-btn">ACC</button>
            <div class="dropdown-content" id="acc-dropdown">
                <a href="#" class="account-settings">Account Settings</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</header>

<main>
<aside class="sidebar">
    <button id="toggleSidebar" class="toggle-sidebar-btn">☰</button>
    <h3>CATEGORIES</h3>
    <ul>
        <li><a href="sus2.php">Sustainable Practices Awareness</a></li>
        <li><a href="eco2.php">Eco-friendly Technology Integration</a></li>
        <li><a href="Community2.php">Community-driven Environmental Action</a></li>
        <li><a href="climates2.php">Climate Change Education</a></li>
        <li><a href="Bio2.php">Biodiversity Conservation</a></li>
        <li><a href="environ2.php">Environmental Policy Advocacy</a></li>
    </ul>
</aside>
</aside>
<button id="showSidebar" class="arrow-btn" style="display: none;"><i class="fa-solid fa-chevron-right"></i></button> 





    
    
    

   
<!-- Display Posts -->
<div class="posts-container">
    <h2>Posts</h2>
    <div class="posts-list" id="postsList">
        <?php if (empty($posts)): ?>
            <p>No posts available.</p>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="post-item" id="post-<?php echo $post['id']; ?>">
                    <p><?php echo htmlspecialchars($post['description']); ?></p>
                    <?php if ($post['image']): ?>
                        <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image" class="post-image">
                    <?php endif; ?>
                    <small>Posted on: <?php echo htmlspecialchars($post['created_at']); ?></small>
                    <div class="post-actions">
                    <button class="like-btn" data-post-id="<?php echo $post['id']; ?>">
    <i class="fa-regular fa-thumbs-up"></i> <span class="like-count"><?php echo $post['likes']; ?></span>
</button>
<button class="comment-toggle" data-post-id="<?php echo $post['id']; ?>">
    <i class="fa-regular fa-comment-dots"></i> 
</button>
                        
                    </div>
                    <div class="comment-section" id="comment-section-<?php echo $post['id']; ?>" style="display: none;">
    <textarea placeholder="Write a comment..." class="comment-input" data-post-id="<?php echo $post['id']; ?>"></textarea>
    <button class="submit-comment" data-post-id="<?php echo $post['id']; ?>">Submit</button>

    <div class="comments-list" id="comments-list-<?php echo $post['id']; ?>">
    <?php foreach ($post['comments'] as $comment): ?>
        <div class="comment-item" id="comment-<?php echo $comment['id']; ?>" style="<?php echo $comment['is_reported'] ? 'color: red;' : ''; ?>">
            <p><?php echo htmlspecialchars($comment['comment']); ?></p>
            <small>Posted by: <?php echo htmlspecialchars($comment['full_name']); ?></small> <!-- Display student's full name -->
            
            <div class="comment-actions">
                <button class="report-comment" data-comment-id="<?php echo $comment['id']; ?>" <?php echo $comment['is_reported'] ? 'disabled' : ''; ?>>Report</button>
            </div>
        </div>
        <hr class="comment-separator"> <!-- Add a horizontal line for separation -->
    <?php endforeach; ?>
</div>
</div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    // AJAX for liking a post
    document.querySelectorAll('.like-btn').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const likeCountSpan = this.querySelector('.like-count');
            
            // Disable the button to prevent multiple clicks
            this.disabled = true;
            
            fetch('like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ post_id: postId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    likeCountSpan.textContent = data.likes; // Update the like count
                    this.style.color = 'green'; // Change button color to green on like
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error))
            .finally(() => {
                // Re-enable the button after the request is complete
                this.disabled = false;
            });
        });
    });

    // Toggle comment section
    document.querySelectorAll('.comment-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const commentSection = document.getElementById('comment-section-' + postId);
            commentSection.style.display = commentSection.style.display === 'none' ? 'block' : 'none';
        });
    });

// Submit comment
document.querySelectorAll('.submit-comment').forEach(button => {
    button.addEventListener('click', function() {
        const postId = this.getAttribute('data-post-id');
        const commentInput = document.querySelector('.comment-input[data-post-id="' + postId + '"]');
        const commentText = commentInput.value;

        if (commentText.trim() === '') {
            alert('Comment cannot be empty.');
            return;
        }

        // Send an AJAX request to submit the comment
        fetch('comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ post_id: postId, comment: commentText }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Create a new comment element
                const commentsList = document.getElementById('comments-list-' + postId);
                const newComment = document.createElement('div');
                newComment.classList.add('comment-item');
                newComment.innerHTML = `
                    <p>${data.comment.comment}</p>
                    <small>Posted by: ${data.comment.user_email}</small>
                `;
                commentsList.appendChild(newComment); // Add the new comment to the list
                commentInput.value = ''; // Clear the input
            } else {
                alert(data.message); // Show error message if any
            }
        })
        .catch(error => console.error('Error:', error));
    });
});</script>

<script>
    document.getElementById('postForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        const formData = new FormData(this); // Create a FormData object from the form

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Create a new post element
                const newPost = document.createElement('div');
                newPost.classList.add('post-item');
                newPost.id = 'post-' + data.post.id; // Set the ID for the new post
                newPost.innerHTML = `
                    <p>${data.post.description}</p>
                    ${data.post.image ? `<img src="${data.post.image}" alt="Post Image" class="post-image">` : ''}
                    <small>Posted on: ${data.post.created_at}</small>
                `;
                document.getElementById('postsList').prepend(newPost); // Add the new post to the top of the list
                this.reset(); // Reset the form
            } else {
                alert(data.message); // Show error message if any
            }
        })
        .catch(error => console.error('Error:', error));
    });
</script>
</section>
</script>

    <section class="content">


        <!-- Inside the overlay for Account Settings -->
        <div id="accountSettingsOverlay" class="overlay-container">
            <div class="overlay-content">
                <span class="close-overlay" onclick="closeAccountSettings()">&times;</span>
                <h2>Account Settings</h2>
                <p><strong>Email:</strong> <?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'Email not set'; ?></p>
                <p><strong>Full Name:</strong> <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Full name not set'; ?></p>

                <p><strong>Address:</strong> <?php echo isset($_SESSION['address']) ? htmlspecialchars($_SESSION['address']) : 'Address not set'; ?></p>
            </div>
        </div>
    </section>
</main>

<script>

    // Function to close overlay
    function closeOverlay() {
        document.getElementById('overlayContainer').style.display = 'none';
    }

    // Function to open overlay for Account Settings
    document.querySelector('.account-settings').addEventListener('click', function(event) {
        event.preventDefault();
        document.getElementById('accountSettingsOverlay').style.display = 'flex';
    });

    // Function to close overlay for Account Settings
    function closeAccountSettings() {
        document.getElementById('accountSettingsOverlay').style.display = 'none';
    }

    // Close the overlay if clicked outside the content area
    window.onclick = function(event) {
        const overlayContainer = document.getElementById('overlayContainer');
        if (event.target === overlayContainer) {
            overlayContainer.style.display = "none";
        }
    }
    
    document.querySelector('.account-btn').addEventListener('click', function() {
        const dropdown = document.getElementById('acc-dropdown');
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block'; // Toggle display
    });

    // Close the dropdown if clicked outside of it
    window.onclick = function(event) {
        if (!event.target.matches('.account-btn')) {
            const dropdowns = document.getElementsByClassName("dropdown-content");
            for (let i = 0; i < dropdowns.length; i++) {
                dropdowns[i].style.display = "none"; // Hide all dropdowns
            }
        }
    }
</script>
<script>
    document.getElementById('toggleSidebar').addEventListener('click', function() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('collapsed'); // Toggle the 'collapsed' class

        const showSidebarBtn = document.getElementById('showSidebar');
        if (sidebar.classList.contains('collapsed')) {
            showSidebarBtn.style.display = 'block'; // Show the arrow button
        } else {
            showSidebarBtn.style.display = 'none'; // Hide the arrow button
        }
    });

    document.getElementById('showSidebar').addEventListener('click', function() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.remove('collapsed'); // Show the sidebar

        const showSidebarBtn = document.getElementById('showSidebar');
        showSidebarBtn.style.display = 'none'; // Hide the arrow button
    });
</script>
<script>
// Report comment functionality
document.querySelectorAll('.report-comment').forEach(button => {
    button.addEventListener('click', function() {
        const commentId = this.getAttribute('data-comment-id');
        
        // Disable the button to prevent multiple clicks
        this.disabled = true;

        fetch('report_comment.php', { // Change to report_comment.php
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ comment_id: commentId }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Turn the comment red
                const commentItem = this.closest('.comment-item'); // Find the parent comment item
                commentItem.style.color = 'red'; // Change text color to red
                this.textContent = 'Reported'; // Optionally change button text
                this.disabled = true; // Disable the button after reporting
            } else {
                alert(data.message); // Show error message if any
            }
        })
        .catch(error => console.error('Error:', error))
        .finally(() => {
            // Re-enable the button after the request is complete (if needed)
            this.disabled = false;
        });
    });
});</script>
</body>
</html>