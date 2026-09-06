    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section about">
                    <h2 class="logo-text">CaRs</h2>
                    <p>Your trusted partner for car rentals. Discover the perfect car for your journey with our extensive fleet and exceptional service.</p>
                    <div class="contact">
                        <span><i class="fas fa-phone"></i> &nbsp; 123-456-7890</span>
                        <span><i class="fas fa-envelope"></i> &nbsp; info@cars.com</span>
                    </div>
                    <div class="socials">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-section links">
                    <h2>Quick Links</h2>
                    <ul class="footer-links">
                        <li><a href="index.php" class="footer-link">Home</a></li>
                        <li><a href="cardetails.php" class="footer-link">Cars</a></li>
                        <li><a href="booking.php" class="footer-link">My Bookings</a></li>
                        <li><a href="feedback.php" class="footer-link">Feedback</a></li>
                        <li><a href="profile.php" class="footer-link">Profile</a></li>
                    </ul>
                </div>
                <div class="footer-section contact-form">
                    <h2>Contact Us</h2>
                    <form action="#" method="post" class="footer-form">
                        <div class="form-group">
                            <input type="email" name="email" class="form-control" placeholder="Your email address..." required>
                        </div>
                        <div class="form-group">
                            <textarea name="message" class="form-control" placeholder="Your message..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; <?php echo date('Y'); ?> CaRs - Car Rental System | All rights reserved
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Mobile menu toggle
            $('.hamburger').click(function() {
                $('.nav-menu').toggleClass('active');
            });

            // Close menu when clicking outside
            $(document).click(function(event) {
                if (!$(event.target).closest('.nav-container').length) {
                    $('.nav-menu').removeClass('active');
                }
            });

            // Smooth scroll for footer links
            $('.footer-link').click(function(e) {
                e.preventDefault();
                const href = $(this).attr('href');
                $('html, body').animate({
                    scrollTop: $(href).offset().top
                }, 800);
            });

            // Form validation
            $('.footer-form').submit(function(e) {
                const email = $('input[name="email"]').val();
                const message = $('textarea[name="message"]').val();
                
                if (!email || !message) {
                    e.preventDefault();
                    alert('Please fill in all fields');
                }
            });
        });
    </script>

    <style>
        .main-footer {
            background: #1a1a1a;
            color: #fff;
            padding: 60px 0 20px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section h2 {
            color: #fff;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }

        .about p {
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .contact span {
            display: block;
            margin-bottom: 10px;
        }

        .socials {
            margin-top: 20px;
        }

        .social-link {
            display: inline-block;
            width: 35px;
            height: 35px;
            background: #333;
            color: #fff;
            text-align: center;
            line-height: 35px;
            border-radius: 50%;
            margin-right: 10px;
            transition: all 0.3s;
        }

        .social-link:hover {
            background: #007bff;
            transform: translateY(-3px);
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-link {
            color: #fff;
            text-decoration: none;
            display: block;
            padding: 8px 0;
            transition: all 0.3s;
        }

        .footer-link:hover {
            color: #007bff;
            padding-left: 10px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #333;
            background: #333;
            color: #fff;
            border-radius: 5px;
        }

        .form-control::placeholder {
            color: #aaa;
        }

        textarea.form-control {
            height: 100px;
            resize: vertical;
        }

        .btn-primary {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #333;
        }

        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
            }
            
            .footer-section {
                text-align: center;
            }

            .contact span {
                justify-content: center;
            }

            .socials {
                justify-content: center;
            }
        }
    </style>
</body>
</html>