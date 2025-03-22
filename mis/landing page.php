<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestlink College</title>
    <link rel="shortcut icon" href="./uploads/blogo.png" type="x-icon">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 20px;
            min-height: 100vh;
            justify-content: space-between;
            overflow-x: hidden;
            background-color: aliceblue;
            background-image: url('uploads/mvb.jpg');
            background-size: cover;
        }

        header {
            background-color: #191970;
            width: 100%;
            padding: 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            color: white;
            font-size: 30px;
            font-weight: bold;
            margin-left: 110px;
        }
        .img1 {
            width: 7%;
            position: absolute;
            margin-left: -25px;
        }
        .menu {
            display: flex;
            gap: 20px;
            list-style: none;
        }
        .menu a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            cursor: pointer;
        }

        /* Vision & Mission Section */
        .bcpvm {
            width: 100%;
            max-width: 1000px;
            margin: 20px auto;
            background-color: white;
            box-shadow: 2px 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 24px;
            padding: 30px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 30px;
            position: relative;
            transition: transform 0.5s ease-in-out;
        }
        .bcpvm h1 {
            font-size: 2rem;
            width: 100%;
            color: #333;
        }
        .vision, .mission {
            background-color: #5218fa;
            width: 48%;
            padding: 40px;
            color: white;
            border-radius: 12px;
            box-shadow: 1px 3px 6px 1px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease-in-out;
            text-align: center;
        }
        .vision:hover, .mission:hover {
            transform: scale(1.02);
        }

        /* Slide Effect */
        .bcpvm.move-left {
            transform: translateX(-130%);
        }

        /* About & Programs Sections */
        .section {
            width: 100%;
            max-width: 1000px;
            margin: 20px auto;
            background: white;
            box-shadow: 2px 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 24px;
            padding: 30px;
            text-align: center;
            position: absolute;
            left: -100%;
            top: 50%;
            transform: translateY(-50%);
            transition: left 0.5s ease-in-out;
        }
       
        .section ul {
            list-style: none;
            padding-left: 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .section ul li {
            font-size: 18px;
            padding: 8px 0;
            width: 48%; /* Ensures two equal columns */
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section ul li::before {
            content: "✔";
            color: green;
            font-weight: bold;
        }
        .section.show {
            left: 50%;
            transform: translate(-50%, -50%);
        }

        /* Hide other sections when one is active */
        .hide {
            display: none;
        }

        footer {
            background: #003366;
            color: white;
            text-align: center;
            padding: 30px;
            width: 100%;
        }
    
         /* Campus & Contact Section */
         .cus, .campus {
            background-color: #5218fa;
            padding: 20px;
            margin: 20px 0;
            border-radius: 12px;
            box-shadow: 2px 4px 8px 2px rgba(0, 0, 0, 0.1);
            text-align: center;
            color: white;
        }

        .cus, .campus p {
            font-size: 18px;
        }
        .campus img {
            width: 80%;
            max-width: 600px;
            border-radius: 12px;
            margin-top: 10px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                text-align: center;
            }
            .menu {
                flex-direction: column;
                align-items: center;
            }
            .menu li {
                margin-bottom: 10px;
            }
            .vision, .mission {
                width: 100%;
                padding: 20px;
            }
            .campus img {
                width: 100%;
                max-width: 100%;
            }
            .section {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <header>
        <img src="./uploads/blogo.png" class="img1">
        <div class="logo">Bestlink College of the Philippines</div>
        <ul class="menu">
            <li><a id="about-link">About</a></li>
            <li><a id="programs-link">Programs</a></li>
            <li><a id="contact-link">Contact/Campuses</a></li>
            <li><a href="login.php">Sign In</a></li>
        </ul>
    </header>   

    <div class="bcpvm" id="bcpvm">
        <h1>BCP VISION AND MISSION</h1>
        <div class="vision">
            <h2>VISION</h2><br>
            <p>Bestlink College of the Philippines is  committed to provide and promote equality education which unique 
                and modern and research-based curriculum with delivery system geard towards excellence.</p>
        </div>
        <div class="mission">
            <h2>MISSION</h2><br>
            <p>To produce a self-motivated and self-directed individual who aims for academic excellence, 
                god-fearing, peaceful, healthy, productive and successful citizen.</p>
        </div>
    </div>

    <!-- About Section -->
    <div class="section" id="about">
        <img src="./uploads/blogo.png" alt="BCP Logo" style="width: 10%;">
        <h2>About BCP</h2><br><br>
        <p style="font-size: 20px;">At Bestlink College of the Philippines, We provide and promote quality education with modern and 
        unique techniques to able to enhance the skill and the knowledge of our dear students to make them globally competitive and productive citizens.</p>
    </div>

    <!-- Contact Section -->
    <div class="section" id="contact" style="overflow-y: auto; max-height: 57%; margin-top: 30px;">
        <h2>Contact and Campuses</h2><br><br>
        <div class="cus">
            <h3>Contact Us</h3><br>
            <p>#1071 Brgy. Kaligayahan, Quirino Highway
            Novaliches Quezon City, Philippines 1123</p><br>

            <p>Contact #: 417-4355 <br><br>
            Email: bcp-inquiry@bcp.edu.ph</p>
        </div>
        <div class="campus">
            <h3>BCP Campuses</h3><br>
            <img src="./uploads/all.jpg" alt="Millionaire's Village Campus"><br><br>
            <p>Millionaire's Village Campus</p><br>
            <p>Main Campus</p><br>
            <p>Bulacan Campus</p><br>
        </div>
    </div>

    <!-- Programs Section -->
    <div class="section" id="programs">
        <h2>Our Programs</h2><br>
        <ul>
            <li> Bachelor of Science in Information Technology</li>
            <li> Bachelor of Science in Business Administration</li>
            <li> Bachelor of Science in Psychology</li>
            <li> Bachelor of Science in Criminology</li>
            <li> Bachelor of Science in Elementary Education</li>
            <li> Bachelor of Science in Secondary Education</li>
            <li> Bachelor of Science in Tourism Management</li>
            <li> Bachelor of Science in Physical Education</li>
            <li> Bachelor of Science in Computer Engineering</li>
            <li> Bachelor of Science in Entrepreneurship</li>
            <li> Bachelor of Science in Accounting Information System</li>
            <li> Bachelor of Science in Technological and Livelihood Education</li>
            <li> Bachelor of Science in Information Science</li>
            <li> Bachelor of Science in Office Administration</li>
        </ul>
    </div>
    <footer>All Rights Reserved 2025. BCP</footer>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const bcpvm = document.querySelector("#bcpvm");
            const aboutSection = document.querySelector("#about");
            const programsSection = document.querySelector("#programs");
            const contactSection = document.querySelector("#contact");
            const aboutLink = document.querySelector("#about-link");
            const contactLink = document.querySelector("#contact-link");
            const programsLink = document.querySelector("#programs-link");

            function closeAll() {
                aboutSection.classList.remove("show");
                programsSection.classList.remove("show");
                contactSection.classList.remove("show");
                bcpvm.classList.remove("move-left");
            }

            aboutLink.addEventListener("click", function () {
                if (!aboutSection.classList.contains("show")) {
                    closeAll();
                    aboutSection.classList.add("show");
                    bcpvm.classList.add("move-left");
                } else {
                    aboutSection.classList.remove("show");
                    bcpvm.classList.remove("move-left");
                }
            });

            programsLink.addEventListener("click", function () {
                if (!programsSection.classList.contains("show")) {
                    closeAll();
                    programsSection.classList.add("show");
                    bcpvm.classList.add("move-left");
                } else {
                    programsSection.classList.remove("show");
                    bcpvm.classList.remove("move-left");
                }
            });

            contactLink.addEventListener("click", function () {
                if (!contactSection.classList.contains("show")) {
                    closeAll();
                    contactSection.classList.add("show");
                    bcpvm.classList.add("move-left");
                } else {
                    contactSection.classList.remove("show");
                    bcpvm.classList.remove("move-left");
                }
            });
        });
    </script>
</body>
</html>