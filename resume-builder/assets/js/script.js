// Основной JavaScript файл для взаимодействия с API

class ResumeBuilder {
    constructor() {
        this.apiBaseUrl = 'api/';
        this.currentResumeId = null;
        this.init();
    }

    init() {
        // Инициализация событий
        this.bindEvents();
        this.loadResumes();
    }

    bindEvents() {
        // Обработка формы создания резюме
        const createForm = document.getElementById('create-resume-form');
        if (createForm) {
            createForm.addEventListener('submit', (e) => this.handleCreateResume(e));
        }

        // Обработка формы персональной информации
        const personalForm = document.getElementById('personal-info-form');
        if (personalForm) {
            personalForm.addEventListener('submit', (e) => this.handlePersonalInfo(e));
        }

        // Кнопка сохранения
        const saveBtn = document.getElementById('save-resume');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.saveResume());
        }

        // Кнопка экспорта PDF
        const exportBtn = document.getElementById('export-pdf');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => this.exportToPDF());
        }
    }

    async loadResumes() {
        try {
            const response = await this.apiRequest('GET', 'resume_api.php');
            const resumes = await response.json();
            this.displayResumes(resumes);
        } catch (error) {
            console.error('Ошибка загрузки резюме:', error);
        }
    }

    displayResumes(resumes) {
        const container = document.getElementById('resumes-container');
        if (!container) return;

        if (resumes.length === 0) {
            container.innerHTML = '<div class="alert alert-info">У вас пока нет резюме</div>';
            return;
        }

        let html = '<div class="row">';
        resumes.forEach(resume => {
            html += `
                <div class="col-md-4 mb-4">
                    <div class="card resume-card">
                        <div class="card-body">
                            <h5 class="card-title">${this.escapeHtml(resume.title)}</h5>
                            <p class="card-text">
                                <small class="text-muted">
                                    Обновлено: ${new Date(resume.updated_at).toLocaleDateString()}
                                </small>
                            </p>
                            <div class="btn-group">
                                <a href="edit_resume.php?id=${resume.id}" class="btn btn-sm btn-outline-primary">Редактировать</a>
                                <a href="view_resume.php?id=${resume.id}" class="btn btn-sm btn-outline-secondary">Просмотр</a>
                                <button onclick="resumeBuilder.deleteResume(${resume.id})" class="btn btn-sm btn-outline-danger">Удалить</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    async handleCreateResume(e) {
        e.preventDefault();
        
        const title = document.getElementById('resume-title').value;
        const template = document.getElementById('resume-template').value;

        try {
            const response = await this.apiRequest('POST', 'resume_api.php', {
                title: title,
                template: template
            });

            const result = await response.json();
            
            if (result.success) {
                window.location.href = `edit_resume.php?id=${result.resume_id}`;
            } else {
                this.showAlert('danger', result.error || 'Ошибка при создании резюме');
            }
        } catch (error) {
            this.showAlert('danger', 'Ошибка сети: ' + error.message);
        }
    }

    async handlePersonalInfo(e) {
        e.preventDefault();
        
        const formData = {
            full_name: document.getElementById('full-name').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            summary: document.getElementById('summary').value
        };

        try {
            const response = await this.apiRequest('PUT', 'personal_info_api.php', formData);
            const result = await response.json();
            
            if (result.success) {
                this.showAlert('success', 'Информация сохранена');
                this.updatePreview();
            } else {
                this.showAlert('danger', result.error);
            }
        } catch (error) {
            this.showAlert('danger', 'Ошибка сохранения: ' + error.message);
        }
    }

    async deleteResume(resumeId) {
        if (!confirm('Вы уверены, что хотите удалить это резюме?')) {
            return;
        }

        try {
            const response = await this.apiRequest('DELETE', `resume_api.php?id=${resumeId}`);
            const result = await response.json();
            
            if (result.success) {
                this.showAlert('success', 'Резюме удалено');
                this.loadResumes();
            } else {
                this.showAlert('danger', result.error);
            }
        } catch (error) {
            this.showAlert('danger', 'Ошибка удаления: ' + error.message);
        }
    }

    async saveResume() {
        // Сохранение всех данных резюме
        try {
            // Собираем все данные из форм
            const resumeData = {
                title: document.getElementById('resume-title')?.value,
                template: document.getElementById('resume-template')?.value
            };

            // Отправляем запрос на сохранение
            const response = await this.apiRequest('PUT', 'resume_api.php', resumeData);
            const result = await response.json();
            
            if (result.success) {
                this.showAlert('success', 'Резюме сохранено');
            } else {
                this.showAlert('danger', result.error);
            }
        } catch (error) {
            this.showAlert('danger', 'Ошибка сохранения: ' + error.message);
        }
    }

    async exportToPDF() {
        try {
            const resumeId = this.getCurrentResumeId();
            if (!resumeId) return;

            // Используем jsPDF для создания PDF
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Получаем HTML резюме
            const resumeContent = document.getElementById('resume-preview').innerHTML;
            
            // Конвертируем в PDF (упрощенный вариант)
            doc.text('Ваше резюме', 20, 20);
            // Здесь должна быть более сложная логика конвертации HTML в PDF
            
            doc.save(`resume-${resumeId}.pdf`);
            
            this.showAlert('success', 'PDF успешно создан');
        } catch (error) {
            console.error('Ошибка создания PDF:', error);
            this.showAlert('danger', 'Ошибка при создании PDF');
        }
    }

    updatePreview() {
        // Обновление предпросмотра резюме в реальном времени
        const preview = document.getElementById('resume-preview');
        if (!preview) return;

        // Собираем данные из форм и обновляем предпросмотр
        const fullName = document.getElementById('full-name')?.value || 'Ваше имя';
        const email = document.getElementById('email')?.value || 'email@example.com';
        
        // Здесь должна быть логика обновления предпросмотра
        // В реальном проекте это было бы более сложно
    }

    async apiRequest(method, endpoint, data = null) {
        const url = this.apiBaseUrl + endpoint;
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        };

        if (data) {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(url, options);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return response;
    }

    showAlert(type, message) {
        // Удаляем существующие алерты
        const existingAlerts = document.querySelectorAll('.alert-dismissible');
        existingAlerts.forEach(alert => alert.remove());

        // Создаем новый алерт
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Добавляем алерт в начало body
        document.body.prepend(alertDiv);

        // Автоматическое скрытие через 5 секунд
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    getCurrentResumeId() {
        // Получение ID текущего резюме из URL или других источников
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('id') || this.currentResumeId;
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    window.resumeBuilder = new ResumeBuilder();
});

// Вспомогательные функции для работы с формами
function addExperience() {
    // Логика добавления нового опыта работы
    const modal = new bootstrap.Modal(document.getElementById('experienceModal'));
    modal.show();
}

function addEducation() {
    // Логика добавления образования
    console.log('Добавление образования');
}

function addSkill() {
    // Логика добавления навыка
    const skillName = prompt('Введите название навыка:');
    if (skillName) {
        const proficiency = prompt('Уровень владения (Начальный/Средний/Продвинутый/Эксперт):');
        // Отправка на сервер
    }
}