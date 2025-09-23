// Admin global alert helper
function alert(type, msg, position = 'body') {
  let bs_class = (type == 'success') ? 'alert-success' : 'alert-danger';
  let element = document.createElement('div');
  element.innerHTML = `
   <div class="alert ${bs_class} alert-dismissible fade show custom-alert" role="alert">
                  <strong class="me-3">${msg}</strong> 
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
              `;
  if (position === 'body') {
      document.body.append(element);
      element.classList.add('custom-alert');
  } else {
      const holder = document.getElementById(position);
      if (holder) holder.appendChild(element);
  }
  setTimeout(remAlert, 2000);
}

function remAlert() {
  const el = document.getElementsByClassName('alert')[0];
  if (el) el.remove();
}