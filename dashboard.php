<?php
session_start();
require 'db.php';

// Fetch admin's email based on the provided email
$admin_email = '';
$stmt = $pdo->query("SELECT * FROM admins WHERE email = 'greenhorizon91@gmail.com' LIMIT 1");
if ($stmt) {
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    $admin_email = $admin['email'] ?? 'admin email not set'; // Default value if email is not found
}

$students = [];
$stmt = $pdo->query("SELECT email, full_name, contact_number, address, registration_date FROM students");
if ($stmt) {
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_description'])) {
    $description = $_POST['post_description'] ?? '';

    $imagePath = null;
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['post_image']['tmp_name'];
        $fileName = $_FILES['post_image']['name'];
        $uploadFileDir = 'uploads/';
        $dest_path = $uploadFileDir . basename($fileName);

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $imagePath = $dest_path;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'File upload failed.']);
            exit;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO posts (description, image) VALUES (:description, :image)");
        $stmt->execute([
            ':description' => $description,
            ':image' => $imagePath
        ]);

        $newPostId = $pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id");
        $stmt->execute([':id' => $newPostId]);
        $newPost = $stmt->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'post' => $newPost]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

$posts = [];
$stmt = $pdo->query("SELECT posts.*, COUNT(posts_likes.id) as likes FROM posts LEFT JOIN posts_likes ON posts.id = posts_likes.post_id GROUP BY posts.id ORDER BY posts.created_at DESC");
if ($stmt) {
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

foreach ($posts as &$post) {
    $postId = $post['id'];
    $commentStmt = $pdo->prepare("
        SELECT comments.*, students.full_name, 
               (SELECT COUNT(*) FROM reported_comments 
                WHERE reported_comments.comment_id = comments.id) AS is_reported
        FROM comments 
        JOIN students ON comments.user_email = students.email 
        WHERE comments.post_id = :post_id 
        ORDER BY comments.created_at DESC
    ");
    $commentStmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
    $commentStmt->execute();
    $post['comments'] = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
}if (isset($_POST['like'])) {
    $postId = $_POST['post_id'];
    $userId = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO posts_likes (user_id, post_id) VALUES (:user_id, :post_id) ON DUPLICATE KEY UPDATE id=id");
    $stmt->execute(['user_id' => $userId, 'post_id' => $postId]);

    header("Location: student_dashboard.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_dashboard_style.css?v=1.1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

</head>
<body>
<header class="navbar">
    <div class="brand">
        <h1>GreenHorizon</h1>
    </div>
    <div class="nav-icons">
        <a href="dashboard.php" class="home-icon">
        <i class="fa-solid fa-house fa-xl" style="color: #228b22;"></i>
        </a>
        <a href="about.php" class="about-icon">
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
        <li><a href="sustainable_practices.php">Sustainable Practices Awareness</a></li>
        <li><a href="eco_friendly_technology.php">Eco-friendly Technology Integration</a></li>
        <li><a href="community_driven.php">Community-driven Environmental Action</a></li>
        <li><a href="climate_change.php">Climate Change Education</a></li>
        <li><a href="biodiversity_conservation.php">Biodiversity Conservation</a></li>
        <li><a href="environmental_policy_advocacy.php">Environmental Policy Advocacy</a></li>
    </ul>
</aside>
</aside>
<button id="showSidebar" class="arrow-btn" style="display: none;"><i class="fa-solid fa-chevron-right"></i></button> <!-- Arrow button -->

        <div class="input-box">
    <form id="postForm" method="POST" enctype="multipart/form-data">
        <textarea name="post_description" placeholder="Write your post..." required class="post-textarea"></textarea>
        <label for="post_image" class="file-btn">Choose File</label>
        <input type="file" name="post_image" id="post_image" accept="image/*" class="post-file-input">
        <button type="submit" class="post-btn">Post</button>
    </form>
</div>

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
                        
                       
                        <div class="comments-list" id="comments-list-<?php echo $post['id']; ?>">
                            <?php foreach ($post['comments'] as $comment): ?>
                                <div class="comment-item" id="comment-<?php echo $comment['id']; ?>" style="<?php echo $comment['is_reported'] ? 'color: red;' : ''; ?>">
                                    <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                                    <small>Posted by: <?php echo htmlspecialchars($comment['full_name']); ?></small> <!-- Display student's full name -->
                                    
                                    <div class="comment-actions">
                <button class="remove-comment-btn" data-comment-id="<?php echo $comment['id']; ?>">Delete</button>
                <div class="report-container" style="display: none;">
                    <button class="report-comment" data-comment-id="<?php echo $comment['id']; ?>">Report</button>
                </div>
            </div>
        </div>
        <hr class="comment-separator">
    <?php endforeach; ?>
</div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
  
    document.querySelectorAll('.comment-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const commentSection = document.getElementById('comment-section-' + postId);
            commentSection.style.display = commentSection.style.display === 'none' ? 'block' : 'none';
        });
    });

document.querySelectorAll('.submit-comment').forEach(button => {
    button.addEventListener('click', function() {
        const postId = this.getAttribute('data-post-id');
        const commentInput = document.querySelector('.comment-input[data-post-id="' + postId + '"]');
        const commentText = commentInput.value;

        if (commentText.trim() === '') {
            alert('Comment cannot be empty.');
            return;
        }

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
                const commentsList = document.getElementById('comments-list-' + postId);
                const newComment = document.createElement('div');
                newComment.classList.add('comment-item');
                newComment.innerHTML = `
                    <p>${data.comment.comment}</p>
                    <small>Posted by: ${data.comment.user_email}</small>
                `;
                commentsList.appendChild(newComment);
                commentInput.value = '';
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });
});</script>


<script>
    document.getElementById('postForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(this)

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const newPost = document.createElement('div');
                newPost.classList.add('post-item');
                newPost.id = 'post-' + data.post.id;
                newPost.innerHTML = `
                    <p>${data.post.description}</p>
                    ${data.post.image ? `<img src="${data.post.image}" alt="Post Image" class="post-image">` : ''}
                    <small>Posted on: ${data.post.created_at}</small>
                `;
                document.getElementById('postsList').prepend(newPost);
                this.reset();
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });
</script>
</section>


</section>
            <button id="settingsBtn" class="settings-btn">Settings</button>
            <div id="settingsModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Lists of Student's Registration</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Full Name</th>
                                <th>Contact Number</th>
                                <th>Address</th>
                                <th>Date of Registration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="5">No registered students found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['contact_number']); ?></td>
                                        <td><?php echo htmlspecialchars($student['address']); ?></td>
                                        <td><?php echo htmlspecialchars($student['registration_date']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
          <script>

function closeOverlay() {
    document.getElementById('overlayContainer').style.display = 'none';
}

        function closeOverlay() {
            document.getElementById('overlayContainer').style.display = 'none';
        }

    document.querySelector('.account-settings').addEventListener('click', function(event) {
        event.preventDefault();
        document.getElementById('accountSettingsOverlay').style.display = 'flex';
    });

    function closeAccountSettings() {
        document.getElementById('accountSettingsOverlay').style.display = 'none';
    }

        window.onclick = function(event) {
            const overlayContainer = document.getElementById('overlayContainer');
            if (event.target === overlayContainer) {
                overlayContainer.style.display = "none";
            }
        }
    </script>
<div id="accountSettingsOverlay" class="overlay-container">
    <div class="overlay-content">
        <span class="close-overlay" onclick="closeAccountSettings()">&times;</span>
        <h2>Account Settings</h2>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($admin_email); ?></p>
        

    </div>
    <script>
        document.querySelectorAll('.toggle-subtopics').forEach(item => {
            item.addEventListener('click', event => {
                event.preventDefault();
                const subtopics = item.nextElementSibling;

                document.querySelectorAll('.subtopics').forEach(otherSubtopics => {
                    if (otherSubtopics !== subtopics) {
                        otherSubtopics.classList.remove('show');
                    }
                });

                subtopics.classList.toggle('show');
            });
        });

        var modal = document.getElementById("settingsModal");
        var btn = document.getElementById("settingsBtn");
        var span = document.getElementsByClassName("close")[0];

        btn.onclick = function() {
            modal.style.display = "block";
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
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
<script>// Remove comment
document.querySelectorAll('.remove-comment-btn').forEach(button => {
    button.addEventListener('click', function() {
        const commentId = this.getAttribute('data-comment-id');
        const commentItem = document.getElementById('comment-' + commentId);

        if (confirm('Are you sure you want to remove this comment?')) {
            this.disabled = true; // Disable the button to prevent multiple clicks
            this.innerText = 'Removing...'; // Change button text

            fetch('delete_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ comment_id: commentId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    commentItem.remove(); // Remove the comment from the DOM
                } else {
                    alert(data.message); // Show error message
                    this.disabled = false; // Re-enable the button
                    this.innerText = 'Remove'; // Reset button text
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while removing the comment.');
                this.disabled = false; // Re-enable the button
                this.innerText = 'Remove'; // Reset button text
            });
        }
    });
});</script>
<script>// Remove comment
document.querySelectorAll('.remove-comment-btn').forEach(button => {
    button.addEventListener('click', function() {
        const commentId = this.getAttribute('data-comment-id');
        const commentItem = document.getElementById('comment-' + commentId);

        if (confirm('Are you sure you want to remove this comment?')) {
            this.disabled = true; // Disable the button to prevent multiple clicks
            this.innerText = 'Removing...'; // Change button text

            fetch('delete_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ comment_id: commentId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    commentItem.remove(); // Remove the comment from the DOM
                } else {
                    alert(data.message); // Show error message if any
                    this.disabled = false; // Re-enable the button
                    this.innerText = 'Delete'; // Reset button text
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while removing the comment.');
                this.disabled = false; // Re-enable the button
                this.innerText = 'Delete'; // Reset button text
            });
        }
    });
});</script>
</body>
</html>
