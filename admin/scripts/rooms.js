let arooms_s_form = document.getElementById('arooms_s_form');


        arooms_s_form.addEventListener('submit', function(e) {
            e.preventDefault();
            add_room();
        });

        function add_room() {
            let data = new FormData();
            data.append('add_room', '');
            data.append('name', arooms_s_form.elements['name'].value);
            data.append('area', arooms_s_form.elements['area'].value);
            data.append('price', arooms_s_form.elements['price'].value);
            data.append('quantity', arooms_s_form.elements['quantity'].value);
            data.append('adult', arooms_s_form.elements['adult'].value);
            data.append('child', arooms_s_form.elements['child'].value);
            data.append('desc', arooms_s_form.elements['desc'].value);

            // Collect features
            let features = [];
            arooms_s_form.querySelectorAll("input[name='features']:checked").forEach((el) => {
                if (el.checked) {
                    features.push(el.value);
                }
            });
            data.append('features', JSON.stringify(features));

            // Collect facilities
            let facilities = [];
            arooms_s_form.querySelectorAll("input[name='facilities']:checked").forEach((el) => {
                if (el.checked) {
                    facilities.push(el.value);
                }
            });
            data.append('facilities', JSON.stringify(facilities));

            data.append('add_room', ''); // ✅ match PHP: if(isset($_POST['add_rooms']))

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/rooms.php", true);

            xhr.onload = function() {
                var myModal = document.getElementById('arooms-s');
                var modal = bootstrap.Modal.getInstance(myModal);
                modal.hide();

                if (this.responseText == 1) {
                    alert('success', 'New Room Added!');
                    arooms_s_form.reset();
                    getall_rooms();
                } else {
                    alert('error', 'Server Down!');
                }
            };

            xhr.send(data); // ✅ this should be OUTSIDE onload
        }

        function getall_rooms() {
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/rooms.php", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                document.getElementById('room-data').innerHTML = this.responseText;

            };

            xhr.send('getall_rooms'); // ✅ match PHP: if(isset($_POST['get_rooms']))
        }

        let edit_room_form = document.getElementById('edit_room_form');

        function edit_bar(id) {
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/rooms.php", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                try {
                    let data = JSON.parse(this.responseText); // ⬅️ now we have a variable

                    console.log(data);

                    // Fill inputs
                    edit_room_form.elements['name'].value = data.roomdata.name;
                    edit_room_form.elements['area'].value = data.roomdata.area;
                    edit_room_form.elements['price'].value = data.roomdata.price;
                    edit_room_form.elements['quantity'].value = data.roomdata.quantity;
                    edit_room_form.elements['adult'].value = data.roomdata.adult;
                    edit_room_form.elements['child'].value = data.roomdata.children;
                    edit_room_form.elements['desc'].value = data.roomdata.description;
                    edit_room_form.elements['room_id'].value = data.roomdata.id;

                    // Restore features checkboxes
                    edit_room_form.querySelectorAll("input[name='features']").forEach((el) => {
                        el.checked = data.features.includes(Number(el.value));
                    });


                    // Restore facilities checkboxes
                    edit_room_form.querySelectorAll("input[name='facilities']").forEach((el) => {
                        el.checked = data.facilities.includes(Number(el.value));
                    });

                    // Show the Edit Room modal after data is loaded
                    new bootstrap.Modal(document.getElementById('edit-room')).show();

                } catch (err) {
                    console.error("Invalid JSON or parse error:", this.responseText);
                }
            };

            xhr.send('get_room=' + id);
        }

        edit_room_form.addEventListener('submit', function(e) {
            e.preventDefault();
            submit_edit_room();
        });

        function submit_edit_room() {
            let data = new FormData();
            data.append('edit_room', '');
            data.append('room_id', edit_room_form.elements['room_id'].value);
            data.append('name', edit_room_form.elements['name'].value);
            data.append('area', edit_room_form.elements['area'].value);
            data.append('price', edit_room_form.elements['price'].value);
            data.append('quantity', edit_room_form.elements['quantity'].value);
            data.append('adult', edit_room_form.elements['adult'].value);
            data.append('child', edit_room_form.elements['child'].value);
            data.append('desc', edit_room_form.elements['desc'].value);

            // Collect features
            let features = [];
            edit_room_form.querySelectorAll("input[name='features']:checked").forEach((el) => {
                if (el.checked) {
                    features.push(el.value);
                }
            });
            data.append('features', JSON.stringify(features));

            // Collect facilities
            let facilities = [];
            edit_room_form.querySelectorAll("input[name='facilities']:checked").forEach((el) => {
                if (el.checked) {
                    facilities.push(el.value);
                }
            });
            data.append('facilities', JSON.stringify(facilities));

            data.append('edit_room', ''); // ✅ match PHP: if(isset($_POST['add_rooms']))

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/rooms.php", true);

            xhr.onload = function() {
                var myModal = document.getElementById('edit-room');
                var modal = bootstrap.Modal.getInstance(myModal);
                modal.hide();

                if (this.responseText == 1) {
                    alert('success', 'Edit  Added!');
                    edit_room_form.reset();
                    getall_rooms();
                } else {
                    alert('error', 'Server Down!');
                }
            };

            xhr.send(data); // ✅ this should be OUTSIDE onload
        }

        function toggle_status(id, val) {
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/rooms.php", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (this.responseText == 1) {
                    alert('success', 'Status toggled!');
                    getall_rooms();
                } else {
                    alert('success', 'Server Down!');
                }

            };

            xhr.send('toggle_status=' + id + '&value=' + val); // ✅ match PHP: if(isset($_POST['get_rooms']))
        }

        let add_image_form = document.getElementById('add-image-form');

        add_image_form.addEventListener('submit', function(e) {
            e.preventDefault();
            add_image();
        });

        function add_image() {


            let data = new FormData();
            data.append('image', add_image_form.elements['image'].files[0]);
            data.append('room_id', add_image_form.elements['room_id'].value);
            data.append('add_image', '');

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/rooms.php", true);

            xhr.onload = function() {
                if (this.responseText == 'inv_img') {
                    alert('error', 'Invalid image format', 'image-alert');

                } else if (this.responseText == 'inv_size') {
                    alert('error', 'Invalid image size', 'image-alert');
                } else if (this.responseText == 'upd_failed') {
                    alert('error', 'Image upload failed', 'image-alert');
                } else {
                    alert('success', 'new Image added', 'image-alert');
                    add_image_form.reset();
                    room_images(add_image_form.elements['room_id'].value, document.querySelector('#room-images .modal-title').innerText);

                }

            }


            xhr.send(data);
        }

        function room_images(id, rname) {
            add_image_form.elements['room_id'].value = id;
            document.querySelector('#room-images .modal-title').innerText = rname;
            add_image_form.elements['image'].value = '';

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/rooms.php", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                document.getElementById('room-image-data').innerHTML = this.responseText;

            };

            xhr.send('get_room_images=' + id); // ✅ match PHP: if(isset($_POST['get_rooms']))
        }

        function rem_image(img_id, room_id) {

            let data = new FormData();
            data.append('image_id', img_id);
            data.append('room_id', room_id);
            data.append('rem_image', '');

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/rooms.php", true);

            xhr.onload = function() {
                if (this.responseText == 1) {
                    alert('success', 'Image removed', 'image-alert');
                    room_images(room_id, document.querySelector('#room-images .modal-title').innerText);

                } else {
                    alert('error', 'Image removal failed', 'image-alert');

                }

            }


            xhr.send(data);

        }

        function thumb_image(img_id, room_id) {
            let data = new FormData();
            data.append('image_id', img_id);
            data.append('room_id', room_id);
            data.append('thumb_image', '');

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/rooms.php", true);

            xhr.onload = function() {
                if (this.responseText == 1) {
                    alert('error', 'Image thumbnail change', 'image-alert');
                    room_images(room_id, document.querySelector('#room-images .modal-title').innerText);

                } else {
                    alert('success', 'thumbnail update failed', 'image-alert');

                }

            }


            xhr.send(data);

        }

        function remove_room(room_id) {
            if (confirm("Are you sure you want to remove this room? This action cannot be undone.")) {
                let data = new FormData();
                data.append('room_id', room_id);
                data.append('remove_room', '');

                let xhr = new XMLHttpRequest();
                xhr.open("POST", "ajax/rooms.php", true);

                xhr.onload = function() {
                    if (this.responseText == 1) {
                        alert('success', 'removed successfully');
                        getall_rooms();


                    } else {
                        alert('error', 'room remove failed');

                    }

                }


                xhr.send(data);
            }







        }


        window.onload = function() {
            getall_rooms();

        }