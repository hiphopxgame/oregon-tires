
// Customer reviews data
const customerReviews = [
    {
        name: "Maria Rodriguez",
        rating: 5,
        date: "2 weeks ago",
        review: "Excellent service!  They installed my new tires quickly and the price was very fair. The staff speaks Spanish which made communication easy. Highly recommend!"
    },
    {
        name: "James Thompson",
        rating: 5,
        date: "1 month ago", 
        review: "Been coming here for years. They always do quality work and are honest about what needs to be done. Fixed my brake issue same day."
    },
    {
        name: "Sarah Chen",
        rating: 5,
        date: "3 weeks ago",
        review: "Fast and professional service. Had a flat tire emergency and they got me back on the road in 30 minutes. Great customer service!"
    },
    {
        name: "Roberto Gonzalez",
        rating: 5,
        date: "2 months ago",
        review: "Servicio excelente! Me ayudaron con la alineación de mis llantas. Personal muy amable y precios justos. Los recomiendo."
    },
    {
        name: "Jennifer Smith",
        rating: 4,
        date: "1 week ago",
        review: "Good experience overall. They diagnosed my car trouble quickly and fixed it at a reasonable price. Only complaint was the wait time."
    },
    {
        name: "Miguel Santos",
        rating: 5,
        date: "4 weeks ago",
        review: "Very helpful staff. They explained everything clearly in Spanish and English. Quality tire installation and fair pricing."
    }
];

// Function to render star ratings
function renderStars(rating) {
    let starsHTML = '';
    for (let i = 0; i < 5; i++) {
        if (i < rating) {
            starsHTML += '<i data-lucide="star" class="h-4 w-4 fill-current text-yellow-400"></i>';
        } else {
            starsHTML += '<i data-lucide="star" class="h-4 w-4 text-gray-300"></i>';
        }
    }
    return starsHTML;
}

// Function to populate reviews
function populateReviews() {
    const reviewsContainer = document.getElementById('reviews-container');
    if (!reviewsContainer) return;
    
    // Randomly select 3 reviews
    const shuffled = [...customerReviews].sort(() => 0.5 - Math.random());
    const selectedReviews = shuffled.slice(0, 3);

    reviewsContainer.innerHTML = selectedReviews.map(review => `
        <div class="bg-gray-50 hover:shadow-lg transition-shadow rounded-lg">
            <div class="p-6">
                <div class="flex items-center gap-2 mb-2">
                    <div class="flex">
                        ${renderStars(review.rating)}
                    </div>
                    <span class="bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs">Verified</span>
                </div>
                <h3 class="text-lg font-semibold mb-1">${review.name}</h3>
                <p class="text-sm text-gray-500 mb-4">${review.date}</p>
                <p class="text-gray-600">${review.review}</p>
            </div>
        </div>
    `).join('');

    // Re-initialize Lucide icons for the new content
    if (window.lucide) {
        lucide.createIcons();
    }
}

// Export for global use
window.populateReviews = populateReviews;
