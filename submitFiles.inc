
<div id="drop-area">
    <form id="uploadForm" enctype="multipart/form-data">
        <input type="file" id="fileElem" multiple accept="application/pdf" onchange="handleFiles(this.files)">
        <label class="button" for="fileElem">Drag and Drop Files Here<br/>Click to select PDF files</label>
    </form>
</div>

<div id="gallery"></div>

<script>
    let dropArea = document.getElementById('drop-area');

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Highlight drop area when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => dropArea.classList.add('highlight'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => dropArea.classList.remove('highlight'), false);
    });

    // Handle dropped files
    dropArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        let dt = e.dataTransfer;
        let files = dt.files;

        handleFiles(files);
    }

    function handleFiles(files) {
        ([...files]).forEach(uploadFile);
        ([...files]).forEach(previewFile);
    }

    function uploadFile(file) {
        let url = 'upload.php';  // The PHP file that handles the upload
        let formData = new FormData();
        formData.append('fileToUpload', file);

        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log(data); // You can also display this in the UI if needed
            if (data.includes('has been uploaded')) { // Check if the upload was successful
                // If the upload was successful, refresh the parent page
                window.parent.location.href = window.parent.location.href;
            } else {
                console.error("Error in file upload:", data); // Log any errors
            }
        })
        .catch(() => console.log("Error uploading file."));
    }

    function previewFile(file) {
        let gallery = document.getElementById('gallery');
        let filePreview = document.createElement('p');
        filePreview.textContent = `Selected file: ${file.name}`;
        gallery.appendChild(filePreview);
    }
</script>

</body>
</html>
