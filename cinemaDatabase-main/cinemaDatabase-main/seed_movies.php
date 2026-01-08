<?php
// seed_movies.php - One-time script to populate the database with 30 movies
// Visit this file once to insert all movies, then you can delete it

require_once __DIR__ . '/db.php';
header('Content-Type: text/html; charset=utf-8');

// Movie data
$movies = [
    ['title' => 'Dune: Part Two', 'director' => 'Denis Villeneuve', 'year' => 2024, 'description' => 'Paul Atreides unites with the Fremen to wage war against House Harkonnen and fulfill his destiny on Arrakis.', 'price' => 380],
    ['title' => 'Deadpool & Wolverine', 'director' => 'Shawn Levy', 'year' => 2024, 'description' => 'Deadpool teams up with Wolverine in a multiverse-altering mission filled with chaos and humor.', 'price' => 400],
    ['title' => 'Inside Out 2', 'director' => 'Kelsey Mann', 'year' => 2024, 'description' => 'Riley enters her teenage years, introducing new emotions that disrupt headquarters.', 'price' => 350],
    ['title' => 'Godzilla x Kong: The New Empire', 'director' => 'Adam Wingard', 'year' => 2024, 'description' => 'Godzilla and Kong face a colossal new threat hidden within the Hollow Earth.', 'price' => 370],
    ['title' => 'Kung Fu Panda 4', 'director' => 'Mike Mitchell', 'year' => 2024, 'description' => 'Po faces a new villain while training a successor as the Dragon Warrior.', 'price' => 330],
    ['title' => 'The Fall Guy', 'director' => 'David Leitch', 'year' => 2024, 'description' => 'A stuntman is pulled back into action to solve a real-life mystery.', 'price' => 360],
    ['title' => 'Furiosa: A Mad Max Saga', 'director' => 'George Miller', 'year' => 2024, 'description' => 'The origin story of Furiosa before the events of Mad Max: Fury Road.', 'price' => 390],
    ['title' => 'Kingdom of the Planet of the Apes', 'director' => 'Wes Ball', 'year' => 2024, 'description' => 'Apes dominate Earth while humans struggle to survive in a new era.', 'price' => 370],
    ['title' => 'The Marvels', 'director' => 'Nia DaCosta', 'year' => 2023, 'description' => 'Captain Marvel teams up with Ms. Marvel and Monica Rambeau to stop a cosmic threat.', 'price' => 360],
    ['title' => 'Aquaman and the Lost Kingdom', 'director' => 'James Wan', 'year' => 2023, 'description' => 'Aquaman must ally with his brother to protect Atlantis from destruction.', 'price' => 360],
    ['title' => 'Wonka', 'director' => 'Paul King', 'year' => 2023, 'description' => 'A whimsical origin story of Willy Wonka and his journey into chocolate-making.', 'price' => 320],
    ['title' => 'Napoleon', 'director' => 'Ridley Scott', 'year' => 2023, 'description' => 'A historical epic chronicling the rise and fall of Napoleon Bonaparte.', 'price' => 380],
    ['title' => 'Oppenheimer', 'director' => 'Christopher Nolan', 'year' => 2023, 'description' => 'The story of J. Robert Oppenheimer and the creation of the atomic bomb.', 'price' => 400],
    ['title' => 'Barbie', 'director' => 'Greta Gerwig', 'year' => 2023, 'description' => 'Barbie embarks on a journey of self-discovery beyond her perfect world.', 'price' => 350],
    ['title' => 'Mission: Impossible – Dead Reckoning Part One', 'director' => 'Christopher McQuarrie', 'year' => 2023, 'description' => 'Ethan Hunt faces his most dangerous mission yet against a rogue AI.', 'price' => 390],
    ['title' => 'The Hunger Games: The Ballad of Songbirds & Snakes', 'director' => 'Francis Lawrence', 'year' => 2023, 'description' => 'A prequel exploring the rise of President Snow.', 'price' => 360],
    ['title' => 'Five Nights at Freddy\'s', 'director' => 'Emma Tammi', 'year' => 2023, 'description' => 'A night security guard uncovers terrifying secrets inside a haunted restaurant.', 'price' => 340],
    ['title' => 'Transformers: Rise of the Beasts', 'director' => 'Steven Caple Jr.', 'year' => 2023, 'description' => 'Autobots and Maximals unite to stop a global threat.', 'price' => 370],
    ['title' => 'The Creator', 'director' => 'Gareth Edwards', 'year' => 2023, 'description' => 'A future war between humans and AI challenges morality and survival.', 'price' => 360],
    ['title' => 'John Wick: Chapter 4', 'director' => 'Chad Stahelski', 'year' => 2023, 'description' => 'John Wick fights his way to freedom against the High Table.', 'price' => 400],
    ['title' => 'Avatar: The Way of Water', 'director' => 'James Cameron', 'year' => 2022, 'description' => 'Jake Sully and his family explore Pandora\'s oceans while facing new threats.', 'price' => 420],
    ['title' => 'A Quiet Place: Day One', 'director' => 'Michael Sarnoski', 'year' => 2024, 'description' => 'The terrifying beginning of the alien invasion in New York City.', 'price' => 360],
    ['title' => 'The Batman', 'director' => 'Matt Reeves', 'year' => 2022, 'description' => 'Batman uncovers corruption in Gotham while hunting the Riddler.', 'price' => 380],
    ['title' => 'Spider-Man: Across the Spider-Verse', 'director' => 'Joaquim Dos Santos', 'year' => 2023, 'description' => 'Miles Morales navigates the multiverse and difficult choices.', 'price' => 370],
    ['title' => 'Doctor Strange in the Multiverse of Madness', 'director' => 'Sam Raimi', 'year' => 2022, 'description' => 'Doctor Strange explores dangerous alternate realities.', 'price' => 360],
    ['title' => 'Fast X', 'director' => 'Louis Leterrier', 'year' => 2023, 'description' => 'Dom Toretto faces a revenge-fueled enemy from his past.', 'price' => 350],
    ['title' => 'Blue Beetle', 'director' => 'Angel Manuel Soto', 'year' => 2023, 'description' => 'A teenager gains alien-powered armor that changes his life forever.', 'price' => 340],
    ['title' => 'The Nun II', 'director' => 'Michael Chaves', 'year' => 2023, 'description' => 'A demonic force resurfaces in a terrifying sequel.', 'price' => 330],
    ['title' => 'Gran Turismo', 'director' => 'Neill Blomkamp', 'year' => 2023, 'description' => 'A gamer becomes a professional race car driver.', 'price' => 350],
    ['title' => 'Rebel Moon', 'director' => 'Zack Snyder', 'year' => 2023, 'description' => 'A colony fights back against a tyrannical galactic empire.', 'price' => 380],
];

echo '<h1>Movie Database Seeder</h1>';
echo '<p>Inserting 30 movies...</p>';

$inserted = 0;
$errors = [];

foreach ($movies as $movie) {
    $stmt = $mysqli->prepare('INSERT INTO movies (title, director, year, description, price, published) VALUES (?, ?, ?, ?, ?, 1)');
    if (!$stmt) {
        $errors[] = "Failed to prepare statement for '{$movie['title']}': " . $mysqli->error;
        continue;
    }
    
    $stmt->bind_param('ssisi', $movie['title'], $movie['director'], $movie['year'], $movie['description'], $movie['price']);
    
    if ($stmt->execute()) {
        $inserted++;
        echo "<p style='color:green'>✓ Inserted: {$movie['title']}</p>";
    } else {
        $errors[] = "Failed to insert '{$movie['title']}': " . $stmt->error;
    }
    $stmt->close();
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p><strong>Successfully inserted:</strong> $inserted movies</p>";

if (!empty($errors)) {
    echo "<p><strong style='color:red'>Errors:</strong></p><ul>";
    foreach ($errors as $error) {
        echo "<li style='color:red'>$error</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='index.php' style='padding:10px 20px;background:#0b69ff;color:#fff;text-decoration:none;border-radius:8px;display:inline-block'>Go to Admin Dashboard</a></p>";
echo "<p><a href='cinema.php' style='padding:10px 20px;background:#1b6f1b;color:#fff;text-decoration:none;border-radius:8px;display:inline-block'>View Public Site</a></p>";
echo "<p style='color:#888;font-size:0.9rem'>You can now delete this seed_movies.php file.</p>";
