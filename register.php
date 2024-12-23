<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-black min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-800 text-white">
                <h2 class="text-2xl font-bold">Registration Form</h2>
            </div>
            <form id="registrationForm" method="post" action="registration.php" class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="lastname" class="block text-sm font-medium text-gray-700">Last Name<span
                                class="text-red-500">*</span></label>
                        <input type="text" id="lastname" name="lastname" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-500 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email<span
                                class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-500 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="firstname" class="block text-sm font-medium text-gray-700">First Name<span
                                class="text-red-500">*</span></label>
                        <input type="text" id="firstname" name="firstname" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-500 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="contactnumber" class="block text-sm font-medium text-gray-700">Contact Number<span
                                class="text-red-500">*</span></label>
                        <input type="text" id="contactnumber" name="contactnumber" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-500 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="middlename" class="block text-sm font-medium text-gray-700">Middle Name</label>
                        <input type="text" id="middlename" name="middlename"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-500 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700">Gender<span
                                class="text-red-500">*</span></label>
                        <select id="gender" name="gender" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-500 focus:ring-opacity-50">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="prefer_not_to_say">I prefer not to say</option>
                        </select>
                    </div>
                    <div>
                        <label for="region" class="block text-sm font-medium text-gray-700">Region<span
                                class="text-red-500">*</span></label>
                        <select id="region" name="region" onchange="fetch_provinces()" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-500 focus:ring-opacity-50">
                            <option value="">Select Region</option>
                            <?php include 'populate_regions.php'; ?>
                        </select>
                    </div>
                    <div>
                        <label for="province" class="block text-sm font-medium text-gray-700">Province<span
                                class="text-red-500">*</span></label>
                        <select id="province" name="province" onchange="fetch_cities()" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-500 focus:ring-opacity-50">
                            <option value="">Select Province</option>
                        </select>
                    </div>
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700">City<span
                                class="text-red-500">*</span></label>
                        <select id="city" name="city" onchange="fetch_barangays()" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-500 focus:ring-opacity-50">
                            <option value="">Select City</option>
                        </select>
                    </div>
                    <div>
                        <label for="barangay" class="block text-sm font-medium text-gray-700">Barangay<span
                                class="text-red-500">*</span></label>
                        <select id="barangay" name="barangay" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-500 focus:ring-opacity-50">
                            <option value="">Select Barangay</option>
                        </select>
                    </div>
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username<span
                                class="text-red-500">*</span></label>
                        <input type="text" id="username" name="username" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-500 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password<span
                                class="text-red-500">*</span></label>
                        <input type="password" id="password" name="password" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-500 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="checkpass" class="block text-sm font-medium text-gray-700">Confirm Password<span
                                class="text-red-500">*</span></label>
                        <input type="password" id="checkpass" name="checkpass" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-500 focus:ring-opacity-50">
                    </div>
                    <div id="passmessage" style="color: red;"></div>
                    <div class="text-center mt-4">
                        <div class="input-group justify-content-center align-items-center">
                            <input type="checkbox" id="checkcon" name="checkcon">
                            <label for="checkcon" class="ml-2">
                                I agree to the terms and
                                <span id="conditions">
                                    <a href="#termsModal"
                                        style="color: blue; cursor: pointer; text-decoration: none;">conditions</a>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between">
                        <button type="submit" id="submitButton"
                            class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-md shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-75 transition duration-300 ease-in-out">
                            Submit
                        </button>
                        <button type="reset"
                            class="px-4 py-2 bg-gray-300 text-gray-700 font-semibold rounded-md shadow-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-75 transition duration-300 ease-in-out">
                            Reset
                        </button>
                    </div>

                    <input type="hidden" id="sRegion" name="sRegion">
                    <input type="hidden" id="sProvince" name="sProvince">
                    <input type="hidden" id="sCity" name="sCity">
                    <div class="row">
                        <div class="text-center mt-2">
                            Already a member? <span><a href="index.php"
                                    style="color: blue; cursor: pointer; text-decoration: none;">Login </a></span>today
                        </div>
                    </div>
            </form>





        </div>
        <div id="termsModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Terms and Conditions
                                </h3>
                                <div class="mt-2 max-h-96 overflow-y-auto">
                                    <p class="text-sm text-gray-500">
                                        Welcome to Elite Footprints! By accessing and using this website, you agree to
                                        be bound by the following terms and conditions:
                                    </p>
                                    <ol class="list-decimal list-inside mt-2 text-sm text-gray-500">
                                        <li>
                                            <p class="font-semibold">Privacy:</p>
                                            <ul class="list-disc list-inside ml-4">
                                                <li>We are committed to protecting your privacy and will not share your
                                                    personal information with third parties.</li>
                                            </ul>
                                        </li>
                                        <li>
                                            <p class="font-semibold">Payment and Refunds:</p>
                                            <ul class="list-disc list-inside ml-4">
                                                <li>Payment is accepted via major Credit/Debit Cards. All goods remain
                                                    our property until paid for in full. No refunds will be offered once
                                                    services are underway.</li>
                                            </ul>
                                        </li>
                                        <li>
                                            <p class="font-semibold">Delivery:</p>
                                            <ul class="list-disc list-inside ml-4">
                                                <li>We aim to deliver products within the timeframe specified on our
                                                    website. However, delays may occur due to unforeseen circumstances.
                                                </li>
                                            </ul>
                                        </li>
                                        <li>
                                            <p class="font-semibold">Product Information:</p>
                                            <ul class="list-disc list-inside ml-4">
                                                <li>We strive to provide accurate product information, but we do not
                                                    warrant that product descriptions or other content is accurate,
                                                    complete, reliable, current, or error-free.</li>
                                            </ul>
                                        </li>
                                        <li>
                                            <p class="font-semibold">User Accounts:</p>
                                            <ul class="list-disc list-inside ml-4">
                                                <li>You are responsible for maintaining the confidentiality of your
                                                    account and password. You agree to accept responsibility for all
                                                    activities that occur under your account.</li>
                                            </ul>
                                        </li>
                                        <li>
                                            <p class="font-semibold">Intellectual Property:</p>
                                            <ul class="list-disc list-inside ml-4">
                                                <li>All content included on this website, such as text, graphics, logos,
                                                    button icons, images, audio clips, digital downloads, data
                                                    compilations, and software, is the property of [Your Online Shop] or
                                                    its content suppliers and protected by international copyright laws.
                                                </li>
                                            </ul>
                                        </li>
                                        <li>
                                            <p class="font-semibold">Limitation of Liability:</p>
                                            <ul class="list-disc list-inside ml-4">
                                                <li>Elite Footprints will not be liable for any indirect, incidental,
                                                    special, consequential or punitive damages, or any loss of profits
                                                    or revenues.</li>
                                            </ul>
                                        </li>
                                        <li>
                                            <p class="font-semibold">Governing Law:</p>
                                            <ul class="list-disc list-inside ml-4">
                                                <li>These terms and conditions are governed by and construed in
                                                    accordance with the laws of Philippines.</li>
                                            </ul>
                                        </li>
                                        <li>
                                            <p class="font-semibold">Changes to Terms:</p>
                                            <ul class="list-disc list-inside ml-4">
                                                <li>We reserve the right to update, change or replace any part of these
                                                    Terms of Service by posting updates and/or changes to our website.
                                                </li>
                                            </ul>
                                        </li>
                                        <li>
                                            <p class="font-semibold">Contact Us:</p>
                                            <ul class="list-disc list-inside ml-4">
                                                <li>If you have any questions about these terms and conditions, please
                                                    contact us at elitefootprints@gmail.com.</li>
                                            </ul>
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" id="closeModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <!-- JavaScript Code -->
        <script>
            document.getElementById("registrationForm").addEventListener("submit", function (event) {
                event.preventDefault(); // Prevent default form submission
                validateForm();
            });
            const modal = document.getElementById('termsModal');
            const openModalBtn = document.getElementById('conditions');
            const closeModalBtn = document.getElementById('closeModal');

            openModalBtn.addEventListener('click', (e) => {
                e.preventDefault();
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });

            closeModalBtn.addEventListener('click', () => {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            });

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            });

            function validateForm() {
                var password = document.getElementById("password").value;
                var confirmPassword = document.getElementById("checkpass").value;
                var form = document.getElementById("registrationForm");

                // Reset styles
                var inputs = form.querySelectorAll("input, select");
                inputs.forEach(function (input) {
                    input.style.border = '1px solid #ccc';
                });
                document.getElementById('checkcon').style.outline = 'none';

                // Check if passwords match
                if (password !== confirmPassword) {
                    document.getElementById("passmessage").innerText = "Passwords do not match";
                    return;
                }

                // Check if password meets criteria
                if (password.length < 8 || !/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/\d/.test(password)) {
                    document.getElementById("passmessage").innerText = "Password must be at least 8 characters long and contain uppercase letters, lowercase letters, and numbers.";
                    return;
                }

                // Check if any required field is empty
                var isValid = true;
                inputs.forEach(function (input) {
                    if (input.hasAttribute("required") && !input.value) {
                        input.style.border = '1px solid red';
                        isValid = false;
                    }
                });

                // Check if terms and conditions checkbox is checked
                if (!document.getElementById('checkcon').checked) {
                    document.getElementById('checkcon').style.outline = '1px solid red';
                    isValid = false;
                }

                if (isValid) {
                    var formData = new FormData(form); // Reconstruct FormData object after validation

                    // Submit the form data via AJAX
                    fetch('registration.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.text())
                        .then(data => {
                            // Handle the response from the server
                            alert(data); // Display the response message

                            // Redirect to index.php upon successful registration
                            if (data === 'Congratulations! You are successfully registered') {
                                window.location.href = 'index.php';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                } else {
                    alert('Please fill in all required fields and agree to the terms and conditions.');
                }
            }



            // Fetch regions
            fetch(`https://psgc.gitlab.io/api/regions/`)
                .then(response => response.json())
                .then(data => {

                    const regionsSelect = document.getElementById('region');
                    data.sort((a, b) => a.name.localeCompare(b.name));
                    data.forEach(region => {
                        const option = document.createElement('option');
                        option.value = region.code;
                        option.textContent = region.name;
                        regionsSelect.appendChild(option);
                    });
                });

            // PSGC API: Provinces

            async function fetch_provinces() {
                const regionCode = document.getElementById('region').value;
                const regionName = document.getElementById('region').selectedOptions[0].textContent;
                const sRegionInput = document.getElementById('sRegion');
                sRegionInput.value = regionName;

                // Now proceed with fetching provinces based on the selected region
                const response = await fetch(`https://psgc.gitlab.io/api/regions/${regionCode}/provinces/`);
                const data = await response.json();

                const provincesSelect = document.getElementById('province');
                provincesSelect.innerHTML = '<option selected disabled>Select Province</option>';
                data.sort((a, b) => a.name.localeCompare(b.name));
                data.forEach(province => {
                    const option = document.createElement('option');
                    option.value = province.code;
                    option.textContent = province.name;
                    provincesSelect.appendChild(option);
                });
            }

            // Function to fetch cities based on selected province
            async function fetch_cities() {
                const provinceCode = document.getElementById('province').value;
                const provinceName = document.getElementById('province').selectedOptions[0].textContent;
                const sProvinceInput = document.getElementById('sProvince');
                sProvinceInput.value = provinceName;

                const response = await fetch(`https://psgc.gitlab.io/api/provinces/${provinceCode}/cities-municipalities/`);
                const data = await response.json();

                const citiesSelect = document.getElementById('city');
                citiesSelect.innerHTML = '<option selected disabled>Select City</option>';
                data.sort((a, b) => a.name.localeCompare(b.name));
                data.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.code;
                    option.textContent = city.name;
                    citiesSelect.appendChild(option);
                });
            }

            // Function to fetch barangays based on selected city
            async function fetch_barangays() {
                const cityCode = document.getElementById('city').value;
                const cityName = document.getElementById('city').selectedOptions[0].textContent;
                const sCityInput = document.getElementById('sCity');
                sCityInput.value = cityName;

                const response = await fetch(`https://psgc.gitlab.io/api/cities-municipalities/${cityCode}/barangays/`);
                const data = await response.json();

                const barangaysSelect = document.getElementById('barangay');
                barangaysSelect.innerHTML = '<option selected disabled>Select Barangay</option>';
                data.sort((a, b) => a.name.localeCompare(b.name));
                data.forEach(barangay => {
                    const option = document.createElement('option');
                    option.value = barangay.name;
                    option.textContent = barangay.name;
                    barangaysSelect.appendChild(option);
                });
            }

            // Call fetch_regions function when the page loads
            window.onload = async function () {
                const response = await fetch('https://psgc.gitlab.io/api/regions/');
                const data = await response.json();
                const regionSelect = document.getElementById('region');
                regionSelect.innerHTML = '<option selected disabled>Select Region</option>';
                data.sort((a, b) => a.name.localeCompare(b.name));
                data.forEach(region => {
                    const option = document.createElement('option');
                    option.value = region.code;
                    option.textContent = region.name;
                    regionSelect.appendChild(option);
                });
            };
        </script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>