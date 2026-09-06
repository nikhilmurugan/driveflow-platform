<?php
session_start();
include('connection.php');

// Redirect if not logged in
if (!isset($_SESSION['email'])) {
    header('location: login.php');
    exit();
}

// Get user's name from database
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT FNAME FROM users WHERE EMAIL = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userName = $user ? $user['FNAME'] : 'Account';

// Process feedback submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $userEmail = $_SESSION['email'];
    
    // Basic validation
    if (empty($rating) || empty($comment)) {
        $message = '<div class="alert alert-danger">Please fill in all fields.</div>';
    } else {
        // Insert into database with correct column names
        $stmt = $conn->prepare("INSERT INTO feedback (EMAIL, rating, COMMENT, feedback_date) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sis", $userEmail, $rating, $comment);
        
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Thank you for your feedback! We appreciate your input.</div>';
        } else {
            $message = '<div class="alert alert-danger">Sorry, there was an error submitting your feedback. Please try again later.</div>';
        }
    }
}

// Set page title
$pageTitle = "Feedback - CaRs";

// Add extra styles specific to this page
$extraStyles = <<<EOT
    <style>
    .feedback-hero {
        background: linear-gradient(rgba(65, 88, 208, 0.8), rgba(200, 80, 192, 0.8)), url('images/carbg2.jpg');
        background-size: cover;
        background-position: center;
        height: 50vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .feedback-hero::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 80px;
        background: linear-gradient(to top, #f8f9fa, transparent);
    }

    .feedback-hero-content {
        max-width: 800px;
        padding: 0 2rem;
        position: relative;
        z-index: 1;
    }

    .feedback-hero-content h1 {
        font-size: 3.5rem;
        margin-bottom: 1.5rem;
        font-weight: 700;
        letter-spacing: 1px;
        text-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }

    .feedback-hero-content p {
        font-size: 1.3rem;
        max-width: 700px;
        margin: 0 auto 2rem;
        font-weight: 300;
        line-height: 1.6;
    }

    .section-header {
        text-align: center;
        margin-bottom: 3rem;
        position: relative;
    }

    .section-header h2 {
        font-size: 2.5rem;
        color: var(--dark-color);
        margin-bottom: 1rem;
        position: relative;
        display: inline-block;
    }

    .section-header h2::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 70px;
        height: 4px;
        background: var(--primary-gradient);
        border-radius: 2px;
    }

    .section-header p {
        max-width: 700px;
        margin: 0 auto;
            color: var(--text-color);
        font-size: 1.1rem;
    }

    .feedback-container {
        padding: 5rem 0;
        background: #f8f9fa;
    }

    .feedback-form {
        background: white;
        padding: 2.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        max-width: 600px;
        margin: 0 auto;
    }

    .form-title {
        font-size: 1.8rem;
        color: var(--dark-color);
        margin-bottom: 1.5rem;
        position: relative;
        padding-bottom: 10px;
        text-align: center;
    }

    .form-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 50px;
        height: 3px;
        background: var(--primary-gradient);
        border-radius: 2px;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--dark-color);
            font-weight: 500;
        }

    .form-control {
        width: 100%;
        padding: 0.8rem 1rem;
        border: 1px solid #e0e0e0;
        border-radius: var(--border-radius);
        font-family: inherit;
        font-size: 1rem;
        color: var(--dark-color);
        transition: var(--transition);
    }

    .form-control:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(65, 88, 208, 0.1);
    }

    textarea.form-control {
        min-height: 150px;
        resize: vertical;
    }

    .submit-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.8rem;
        padding: 0.8rem 2rem;
        background: var(--primary-gradient);
        color: #fff;
        border: none;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--transition);
        font-weight: 500;
        font-size: 1rem;
        box-shadow: 0 4px 15px rgba(65, 88, 208, 0.3);
    }

    .submit-btn:hover {
        background: linear-gradient(45deg, #3448a5, #b346ad);
        transform: translateY(-3px);
    }
    
    .alert {
        padding: 1rem;
        border-radius: var(--border-radius);
        margin-bottom: 1.5rem;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    /* Rating Styling */
    .rating-container {
        display: flex;
        flex-direction: row-reverse;
        justify-content: center;
        margin-bottom: 1.5rem;
    }
    
    .rating-container input {
        display: none;
    }
    
    .rating-container label {
        font-size: 2.5rem;
        color: #ddd;
        cursor: pointer;
        padding: 0 0.2rem;
        transition: var(--transition);
    }
    
    .rating-container label:hover,
    .rating-container label:hover ~ label,
    .rating-container input:checked ~ label {
        color: #ffb700;
    }
    
    .rating-text {
            text-align: center;
        margin-bottom: 1rem;
        font-size: 1.1rem;
        color: var(--dark-color);
    }
    
    /* Previous Feedback Section */
    .feedback-list {
        margin-top: 4rem;
    }
    
    .feedback-card {
        background: white;
        padding: 1.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        margin-bottom: 1.5rem;
        position: relative;
        border-left: 4px solid var(--primary-color);
    }
    
    .feedback-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #f5f5f5;
    }
    
    .user-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary-gradient);
            color: white;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .user-name {
        font-weight: 600;
        color: var(--dark-color);
    }
    
    .feedback-rating {
        color: #ffb700;
        font-size: 1.2rem;
    }
    
    .feedback-date {
        font-size: 0.9rem;
        color: #888;
    }
    
    .feedback-comment {
        font-size: 1rem;
        line-height: 1.6;
        color: var(--text-color);
    }
    
    .no-feedback {
        text-align: center;
        padding: 2rem;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
    }
    
    .no-feedback i {
        font-size: 3rem;
        color: #ddd;
        margin-bottom: 1rem;
    }
    
    .no-feedback p {
        font-size: 1.1rem;
        color: #888;
    }
    
    @media (max-width: 768px) {
        .feedback-hero-content h1 {
            font-size: 2.5rem;
        }
        
        .feedback-hero-content p {
            font-size: 1.1rem;
        }
        
        .rating-container label {
            font-size: 2rem;
        }
    }
</style>
EOT;

// Add extra scripts for the feedback page
$extraScripts = <<<EOT
// Update text based on selected rating
document.addEventListener('DOMContentLoaded', function() {
    const ratingLabels = document.querySelectorAll('.rating-container label');
    const ratingText = document.getElementById('rating-text');
    const ratingDescriptions = [
        'Extremely Unsatisfied',
        'Unsatisfied',
        'Neutral',
        'Satisfied',
        'Extremely Satisfied'
    ];
    
    // Set initial text
    if (ratingText) {
        ratingText.textContent = 'Select a Rating';
    }
    
    // Update text on rating selection
    ratingLabels.forEach((label, index) => {
        label.addEventListener('click', function() {
            if (ratingText) {
                ratingText.textContent = ratingDescriptions[4 - index];
            }
        });
    });
});
EOT;

// Include header
include 'header.php';
?>

<section class="feedback-hero">
    <div class="feedback-hero-content">
        <h1>Share Your Feedback</h1>
        <p>Your opinion matters to us. Help us improve our services by sharing your experience with CaRs.</p>
                </div>
</section>

<div class="feedback-container">
    <div class="container">
        <div class="section-header">
            <h2>Tell Us What You Think</h2>
            <p>Your feedback helps us understand what we're doing right and how we can improve to serve you better.</p>
        </div>

        <?php echo $message; ?>

        <div class="feedback-form">
            <h3 class="form-title">Your Feedback</h3>
            <form action="" method="POST">
                <div class="rating-text" id="rating-text">Select a Rating</div>
                <div class="rating-container">
                    <input type="radio" id="star5" name="rating" value="5" required>
                    <label for="star5" title="Excellent"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star4" name="rating" value="4">
                    <label for="star4" title="Very Good"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star3" name="rating" value="3">
                    <label for="star3" title="Good"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star2" name="rating" value="2">
                    <label for="star2" title="Poor"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star1" name="rating" value="1">
                    <label for="star1" title="Very Poor"><i class="fas fa-star"></i></label>
                </div>

                <div class="form-group">
                    <label for="comment" class="form-label">Your Comments</label>
                    <textarea id="comment" name="comment" class="form-control" placeholder="Tell us about your experience with our service..." required></textarea>
                </div>

                <div style="text-align: center;">
                    <button type="submit" name="submit_feedback" class="submit-btn">
                        <i class="fas fa-paper-plane"></i>
                        Submit Feedback
                    </button>
                </div>
            </form>
        </div>

        <!-- Display previous feedback -->
        <div class="feedback-list">
            <div class="section-header">
                <h2>Recent Feedback</h2>
                <p>See what other customers are saying about us</p>
    </div>

            <?php
            // Get recent feedback limited to 5 entries
            $stmt = $conn->prepare("SELECT f.*, u.FNAME, u.LNAME FROM feedback f 
                                   LEFT JOIN users u ON f.EMAIL = u.EMAIL 
                                   ORDER BY f.feedback_date DESC LIMIT 5");
            $stmt->execute();
            $feedbackResult = $stmt->get_result();
            
            if ($feedbackResult->num_rows > 0) {
                while ($feedback = $feedbackResult->fetch_assoc()) {
                    // Get user initials or first name
                    $userInitial = !empty($feedback['FNAME']) ? substr($feedback['FNAME'], 0, 1) : 'U';
                    $userName = !empty($feedback['FNAME']) ? $feedback['FNAME'] . ' ' . substr($feedback['LNAME'], 0, 1) . '.' : 'User';
                    
                    // Format date
                    $date = new DateTime($feedback['feedback_date']);
                    $formattedDate = $date->format('F j, Y');
                    
                    // Generate stars based on rating
                    $stars = '';
                    for ($i = 0; $i < 5; $i++) {
                        if ($i < $feedback['rating']) {
                            $stars .= '<i class="fas fa-star"></i>';
                        } else {
                            $stars .= '<i class="far fa-star"></i>';
                        }
                    }
                    
                    echo '<div class="feedback-card">
                            <div class="feedback-header">
                                <div class="user-info">
                                    <div class="user-avatar">' . $userInitial . '</div>
                                    <span class="user-name">' . $userName . '</span>
                                </div>
                                <div class="feedback-rating">
                                    ' . $stars . '
                                </div>
                            </div>
                            <div class="feedback-date">' . $formattedDate . '</div>
                            <div class="feedback-comment">' . htmlspecialchars($feedback['COMMENT']) . '</div>
                          </div>';
                }
            } else {
                echo '<div class="no-feedback">
                        <i class="far fa-comment-dots"></i>
                        <p>No feedback submitted yet. Be the first to share your experience!</p>
                      </div>';
            }
            ?>
        </div>
    </div>
</div>

<?php
include 'footer.php';
?>
