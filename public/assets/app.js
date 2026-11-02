document.addEventListener('DOMContentLoaded', () => {
  // Helpful character counts for posts and profile bios.
  document.querySelectorAll('textarea[maxlength]').forEach((area) => {
    const counter = document.createElement('small');
    counter.className = 'muted character-count';
    counter.setAttribute('aria-live', 'polite');
    area.insertAdjacentElement('afterend', counter);
    const update = () => {
      counter.textContent = `${area.value.length} / ${area.maxLength} characters`;
      counter.classList.toggle('near-limit', area.maxLength - area.value.length < 80);
    };
    area.addEventListener('input', update);
    update();
  });

  // Show the selected image filename before a post or profile is submitted.
  document.querySelectorAll('input[type="file"]').forEach((input) => {
    input.addEventListener('change', () => {
      const file = input.files && input.files[0];
      let note = input.parentElement.querySelector('.file-name');
      if (!note) {
        note = document.createElement('small');
        note.className = 'file-name muted';
        input.parentElement.appendChild(note);
      }
      note.textContent = file ? `Selected: ${file.name}` : '';
    });
  });
});
