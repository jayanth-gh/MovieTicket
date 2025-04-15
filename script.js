// script.js
document.addEventListener('DOMContentLoaded', () => {
    fetch("php/fetch_movies.php")
        .then(res => res.json())
        .then(data => {
            const moviesGrid = document.getElementById("movies");
            moviesGrid.innerHTML = ''; // Clear existing content

            data.forEach(movie => {
                const movieCard = document.createElement("div");
                movieCard.className = "movie-card";
                
                // Create movie card content
                movieCard.innerHTML = `
                    <img src="${movie.Poster || 'https://via.placeholder.com/300x450?text=No+Image'}" alt="${movie.Title}">
                    <div class="movie-info">
                        <h3>${movie.Title}</h3>
                        <p><i class="fas fa-film"></i> ${movie.Genre}</p>
                        <p><i class="fas fa-clock"></i> ${movie.Duration} min</p>
                        <p><i class="fas fa-language"></i> ${movie.Language}</p>
                        <p><i class="fas fa-calendar"></i> ${new Date(movie.Release_Date).toLocaleDateString()}</p>
                        <a href="book.html" class="book-button">Book Now</a>
                    </div>
                `;
                
                moviesGrid.appendChild(movieCard);
            });
        })
        .catch(error => {
            console.error('Error fetching movies:', error);
            const moviesGrid = document.getElementById("movies");
            moviesGrid.innerHTML = '<p class="error-message">Failed to load movies. Please try again later.</p>';
        });
});
