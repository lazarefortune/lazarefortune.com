import "./toast.scss";

function hideNotification(notification) {
    setTimeout(() => {
        notification.remove();
    }, 300);
}

function showToastNotification({
                                   heading = '',
                                   text = '',
                                   position = 'top-right',
                                   type = 'neutral',
                                   hideAfter = 3000,
                               }) {
    const toastHtml = `
    <div class="toast-notification toast-notification-${position} toast-notification-${type}" role="alert">
      <div class="flex">
        <div class="ml-3">
          <span class="mb-1 toast-notification-heading">
            ${heading}
          </span>
          <div class="toast-notification-content ${heading ? 'mb-2' : ''}">
            ${text}
          </div>
        </div>
        <button type="button" class="toast-notification-close" aria-label="Close">
          <span class="sr-only">Fermer</span>
          <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
          </svg>
        </button>
      </div>
    </div>
  `;

    const toast = document.createElement('div');
    toast.innerHTML = toastHtml;
    document.body.appendChild(toast);

    const toastNotification = toast.querySelector('.toast-notification');
    const toastNotificationClose = toast.querySelector('.toast-notification-close');

    toastNotificationClose.addEventListener('click', () => {
        hideNotification(toastNotification);
    });

    setTimeout(() => {
        hideNotification(toastNotification);
    }, hideAfter);

    return toastNotification;
}

export {showToastNotification};
