

        let sliders_s_form = document.getElementById('sliders_s_form');
        let sliders_picture_inp = document.getElementById('sliders_picture_inp');


        sliders_s_form.addEventListener('submit', function(e) {
            e.preventDefault();
            add_sliders();
        });

        function add_sliders() {
            let data = new FormData();
            data.append('picture', sliders_picture_inp.files[0]);
            data.append('add_sliders', '');

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/sliders_crud.php", true);

            xhr.onload = function() {


                var myModal = document.getElementById('sliders-s');
                var modal = bootstrap.Modal.getInstance(myModal)
                modal.hide();

                if (this.responseText == 'inv_img') {
                    alert('error', 'Member added successfully');

                } else if (this.responseText == 'inv_size') {
                    alert('error', 'Invalid image size');
                } else if (this.responseText == 'upd_failed') {
                    alert('error', 'Image upload failed');
                } else {
                    alert('success', 'new Image added');
                    sliders_picture_inp.value = '';
                    get_sliders();

                }

            }


            xhr.send(data);
        }

        function get_sliders() {

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/sliders_crud.php", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                document.getElementById('sliders-data').innerHTML = this.responseText;

            }





            xhr.send('get_sliders');

        }

        function rem_sliders(val) {
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/sliders_crud.php", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (this.responseText == 1) {
                    alert('success', 'sliders removed');
                    get_sliders();
                } else {
                    aler('error', 'server down!');
                }


            }


            xhr.send('rem_sliders=' + val);
        }




        window.onload = function() {
            get_sliders();
        }
    