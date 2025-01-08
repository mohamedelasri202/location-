let sideLinks = document.querySelectorAll('.sidebar .side-menu li a:not(.logout)');
sideLinks.forEach(item => {
    const li = item.parentElement;
    item.addEventListener('click', (e) => {

        sideLinks.forEach(i => {
            i.parentElement.classList.remove('active');
        })
        li.classList.add('active');
    })
});

let menuBar = document.querySelector('.content nav .bx.bx-menu');
let sideBar = document.querySelector('.sidebar');
menuBar.addEventListener('click', (e) => {
    e.preventDefault();
    sideBar.classList.toggle('close');
});

let searchBtn = document.querySelector('.content nav form .form-input button');
let searchBtnIcon = document.querySelector('.content nav form .form-input button .bx');
let searchForm = document.querySelector('.content nav form');
searchBtn.addEventListener('click', function(e) {
    if (window.innerWidth < 576) {
        e.preventDefault;
        searchForm.classList.toggle('show');
        if (searchForm.classList.contains('show')) {
            searchBtnIcon.classList.replace('bx-search', 'bx-x');
        } else {
            searchBtnIcon.classList.replace('bx-x', 'bx-search');
        }
    }
});
window.addEventListener('resize', () => {
    if (window.innerWidth < 768) {
        sideBar.classList.add('close');
    } else {
        sideBar.classList.remove('close');
    }
    if (window.innerWidth > 576) {
        searchBtnIcon.classList.replace('bx-x', 'bx-search');
        searchForm.classList.remove('show');
    }
});
const toggler = document.getElementById('theme-toggle');

toggler.addEventListener('change', function() {
    if (this.checked) {
        document.body.classList.add('dark');
    } else {
        document.body.classList.remove('dark');
    }
});


document.getElementById('buttonadd').addEventListener('click', function(e) {
    e.preventDefault()
    document.getElementById('addClientForm').classList.add('active');
});


document.getElementById('closeForm').addEventListener('click', function() {
        document.getElementById('addClientForm').classList.remove('active');
    })
    //fooor edit

document.getElementById('colseedit').addEventListener('click', function() {
    document.getElementById('editform').classList.remove('active');
    window.location.href = 'clients.php'
})

document.getElementById('colseedit').addEventListener('click', function() {
    document.getElementById('editformcar').classList.remove('active');

    window.location.href = 'cars.php'
})
document.getElementById('colseedit').addEventListener('click', function() {
    document.getElementById('editcontform').classList.remove('active');
    window.location.href = 'contrats.php'
})

document.querySelector(".DateDebut").addEventListener("change", calculateDuration);
document.querySelector(".DateFin").addEventListener("change", calculateDuration);

function calculateDuration() {
    let startDate = document.querySelector(".DateDebut").value;
    let endDate = document.querySelector(".DateFin").value;

    console.log(startDate);

    if (startDate && endDate) {
        let start = new Date(startDate);
        let end = new Date(endDate);
        let differenceInTime = end - start;
        let differenceInDays = differenceInTime / (1000 * 60 * 60 * 24);
        document.querySelector(".Duree").value = differenceInDays;
    } else {
        document.querySelector(".Duree").value = '';
    }
}
document.querySelector(".DateDebutt").addEventListener("change", calculateDuration);
document.querySelector(".DateFint").addEventListener("change", calculateDuration);

function calculateDuration() {
    let startDate = document.querySelector(".DateDebutt").value;
    let endDate = document.querySelector(".DateFint").value;

    console.log(startDate);

    if (startDate && endDate) {
        let start = new Date(startDate);
        let end = new Date(endDate);
        let differenceInTime = end - start;
        let differenceInDays = differenceInTime / (1000 * 60 * 60 * 24);
        document.querySelector(".Dureet").value = differenceInDays;
    } else {
        document.querySelector(".Dureet").value = '';
    }
}
// Script لإخفاء الـ Alert بعد 10 ثواني
document.addEventListener('DOMContentLoaded', function() {
    // نحدد العنصر ديال الـ Alert
    const alert = document.getElementById('alert-success');

    if (alert) {
        // نخليه يختفي بعد 10 ثواني
        setTimeout(function() {
            alert.classList.add('hidden'); // نضيف كلاس hidden باش نخبّيه
        }, 10000); // 10000ms = 10 ثواني

        // زر الإغلاق (إذا بغى المستخدم يسدّ يدوياً)
        const closeAlert = document.getElementById('close-alert');
        closeAlert.addEventListener('click', function() {
            alert.classList.add('hidden');
        });
    }
});

// ********************************************************
let carCount = 1;

function addCar() {
    const container = document.getElementById('carsContainer');
    const newCar = document.querySelector('.car-input').cloneNode(true);
    
    // Update car number in the heading
    newCar.querySelector('h3').textContent = `Car ${++carCount}`;
    
    // Reset all inputs
    newCar.querySelectorAll('input, textarea, select').forEach(input => {
        input.value = '';
        // Update IDs to maintain uniqueness
        if (input.id) {
            input.id = input.id.replace(/\d+/, carCount - 1);
        }
    });
    
    // Reset image preview
    const imagePreview = newCar.querySelector('.image-preview');
    if (imagePreview) {
        imagePreview.innerHTML = '';
    }
    
    container.appendChild(newCar);
}

function removeCar(element) {
    if (document.querySelectorAll('.car-input').length > 1) {
        element.closest('.car-input').remove();
        updateCarNumbers();
    } else {
        const alertsDiv = document.getElementById('alerts');
        alertsDiv.innerHTML = '<div class="alert alert-error p-4 mb-4 text-red-700 bg-red-100 rounded-lg">You must have at least one car</div>';
    }
}

function updateCarNumbers() {
    document.querySelectorAll('.car-input h3').forEach((header, index) => {
        header.textContent = `Car ${index + 1}`;
    });
    carCount = document.querySelectorAll('.car-input').length;
}

function previewImage(input) {
    const preview = input.parentElement.querySelector('.image-preview');
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'preview-image w-full h-32 object-cover rounded-lg mt-2';
            preview.appendChild(img);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Form submission handler
document.getElementById('carsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const alertsDiv = document.getElementById('alerts');
        alertsDiv.innerHTML = '';

        if (data.errors && data.errors.length > 0) {
            data.errors.forEach(error => {
                alertsDiv.innerHTML += `<div class="alert alert-error">${error}</div>`;
            });
        }

        if (data.success && data.success.length > 0) {
            data.success.forEach(message => {
                alertsDiv.innerHTML += `<div class="alert alert-success">${message}</div>`;
            });
            if (data.status === 'success') {
                setTimeout(() => {
                    window.location.href = 'list_cars.php';
                }, 2000);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('alerts').innerHTML = 
            '<div class="alert alert-error">An error occurred while saving the cars</div>';
    });
});

// Add slide-out form functionality
document.addEventListener('DOMContentLoaded', function() {
    const addButton = document.querySelector('[data-target="addClientForm"]');
    const closeButton = document.getElementById('closeForm');
    const form = document.getElementById('addClientForm');

    if (addButton) {
        addButton.addEventListener('click', () => {
            form.style.right = '0';
        });
    }

    if (closeButton) {
        closeButton.addEventListener('click', () => {
            form.style.right = '-100%';
        });
    }
});