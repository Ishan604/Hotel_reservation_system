const bookingForm = document.getElementById('bookingForm');

bookingForm.addEventListener('submit', function (e) {
  const checkIn = bookingForm.checkIn.value;
  const checkOut = bookingForm.checkOut.value;

  if (checkIn >= checkOut) {
    alert('Check-Out date must be after Check-In date.');
    e.preventDefault();
  } else {
    alert('Your reservation has been submitted successfully!');
  }
});

const roomCards = document.querySelector('.room-cards');
const prevBtn = document.querySelector('.prev-btn');
const nextBtn = document.querySelector('.next-btn');

let currentIndex = 0;
const slideWidth = 320; // Room card width + gap
const totalCards = roomCards.children.length;
const maxIndex = totalCards - 3; // Show only 3 slides at a time

// Move the slide to the left when clicking the "prev" button
prevBtn.addEventListener('click', () => {
  currentIndex = Math.max(currentIndex - 1, 0);
  roomCards.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
});

// Move the slide to the right when clicking the "next" button
nextBtn.addEventListener('click', () => {
  currentIndex = Math.min(currentIndex + 1, maxIndex);
  roomCards.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
});

// Set today's date as the minimum for both check-in and check-out
document.addEventListener("DOMContentLoaded", function() {
  const today = new Date().toISOString().split("T")[0]; // Get current date in YYYY-MM-DD format
  
  // Set minimum date for check-in and check-out inputs
  document.getElementById('checkIn').setAttribute('min', today);
  document.getElementById('checkOut').setAttribute('min', today);
});

document.addEventListener('DOMContentLoaded', function() {
    // Toggle dropdowns on click
    document.querySelectorAll('.dropdown-box').forEach(dropdown => {
        const label = dropdown.querySelector('.dropdown-label');
        const valueDisplay = dropdown.querySelector('.dropdown-value');
        const countDisplay = dropdown.querySelector('.count');
        const decrementBtn = dropdown.querySelector('.decrement');
        const incrementBtn = dropdown.querySelector('.increment');
        const hiddenInput = dropdown.querySelector('input[type="hidden"]');

        let count = parseInt(countDisplay.textContent);

        // Toggle dropdown
        dropdown.addEventListener('click', function(e) {
            if (e.target !== decrementBtn && e.target !== incrementBtn) {
                this.classList.toggle('active');
            }
        });

        // Increment
        incrementBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            count++;
            updateCount();
        });

        // Decrement
        decrementBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (count > 0) {
                count--;
                updateCount();
            }
        });

        function updateCount() {
            countDisplay.textContent = count;
            valueDisplay.textContent = count;
            hiddenInput.value = count;
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown-box')) {
            document.querySelectorAll('.dropdown-box').forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
});






