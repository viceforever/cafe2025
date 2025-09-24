@extends('layouts.app')

@section('title', 'Тест DaData API')

@section('main_content')
<div class="container" style="margin-top: 120px;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Диагностика DaData API</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5>1. Тест API конфигурации</h5>
                        <button id="test-config" class="btn btn-primary">Проверить конфигурацию</button>
                        <div id="config-result" class="mt-3"></div>
                    </div>
                    
                    <div class="mb-4">
                        <h5>2. Тест автодополнения адресов</h5>
                        <div class="position-relative">
                            <input type="text" id="test-address" class="form-control" placeholder="Введите адрес для тестирования..." autocomplete="off">
                            <div id="test-suggestions" class="position-absolute w-100 bg-white border border-top-0 rounded-bottom shadow-sm" style="display: none; z-index: 1000; max-height: 300px; overflow-y: auto;"></div>
                        </div>
                        <div id="address-result" class="mt-3"></div>
                    </div>
                    
                    <div class="mb-4">
                        <h5>3. Логи браузера</h5>
                        <div id="browser-logs" class="bg-light p-3 rounded" style="font-family: monospace; font-size: 12px; max-height: 300px; overflow-y: auto;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const configButton = document.getElementById('test-config');
    const configResult = document.getElementById('config-result');
    const testAddressInput = document.getElementById('test-address');
    const testSuggestions = document.getElementById('test-suggestions');
    const addressResult = document.getElementById('address-result');
    const browserLogs = document.getElementById('browser-logs');
    
    let debounceTimer;
    
    // Перехватываем console.log для отображения в интерфейсе
    const originalLog = console.log;
    const originalError = console.error;
    
    function addLogEntry(type, message) {
        const timestamp = new Date().toLocaleTimeString();
        const logEntry = document.createElement('div');
        logEntry.className = type === 'error' ? 'text-danger' : 'text-info';
        logEntry.textContent = `[${timestamp}] ${type.toUpperCase()}: ${message}`;
        browserLogs.appendChild(logEntry);
        browserLogs.scrollTop = browserLogs.scrollHeight;
    }
    
    console.log = function(...args) {
        originalLog.apply(console, args);
        addLogEntry('log', args.join(' '));
    };
    
    console.error = function(...args) {
        originalError.apply(console, args);
        addLogEntry('error', args.join(' '));
    };
    
    console.log('[v0] DaData diagnostic page loaded');
    
    // Тест конфигурации
    configButton.addEventListener('click', async function() {
        console.log('[v0] Testing DaData configuration...');
        configResult.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Проверяем конфигурацию...';
        
        try {
            const response = await fetch('/api/address/test', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            console.log('[v0] Config test response:', data);
            
            if (data.success) {
                configResult.innerHTML = `
                    <div class="alert alert-success">
                        <h6>✅ Конфигурация работает!</h6>
                        <p><strong>Тестовый запрос:</strong> ${data.test_query}</p>
                        <p><strong>Найдено подсказок:</strong> ${data.suggestions_count}</p>
                        <details>
                            <summary>Подробности ответа</summary>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </details>
                    </div>
                `;
            } else {
                configResult.innerHTML = `
                    <div class="alert alert-danger">
                        <h6>❌ Ошибка конфигурации</h6>
                        <p>${data.message}</p>
                        <details>
                            <summary>Подробности ошибки</summary>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </details>
                    </div>
                `;
            }
        } catch (error) {
            console.error('[v0] Config test error:', error);
            configResult.innerHTML = `
                <div class="alert alert-danger">
                    <h6>❌ Ошибка запроса</h6>
                    <p>${error.message}</p>
                </div>
            `;
        }
    });
    
    // Тест автодополнения
    testAddressInput.addEventListener('input', function() {
        const query = this.value.trim();
        console.log('[v0] Test input changed:', query);
        
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            testAddressSuggestions(query);
        }, 300);
    });
    
    async function testAddressSuggestions(query) {
        if (query.length < 3) {
            testSuggestions.style.display = 'none';
            addressResult.innerHTML = '';
            return;
        }
        
        console.log('[v0] Testing address suggestions for:', query);
        addressResult.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Получаем подсказки...';
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            if (!csrfToken) {
                throw new Error('CSRF token not found');
            }
            
            const response = await fetch('/api/address/suggest', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    query: query,
                    count: 5
                })
            });
            
            console.log('[v0] Test response status:', response.status);
            
            const data = await response.json();
            console.log('[v0] Test response data:', data);
            
            if (data.success && data.suggestions) {
                let html = '';
                data.suggestions.forEach((suggestion, index) => {
                    html += `
                        <div class="suggestion-item p-2 border-bottom">
                            <div class="fw-medium">${suggestion.value}</div>
                            ${suggestion.data.postal_code ? `<small class="text-muted">${suggestion.data.postal_code}</small>` : ''}
                        </div>
                    `;
                });
                
                testSuggestions.innerHTML = html;
                testSuggestions.style.display = 'block';
                
                addressResult.innerHTML = `
                    <div class="alert alert-success">
                        <h6>✅ Подсказки получены!</h6>
                        <p><strong>Найдено:</strong> ${data.suggestions.length} подсказок</p>
                        <details>
                            <summary>Debug информация</summary>
                            <pre>${JSON.stringify(data.debug, null, 2)}</pre>
                        </details>
                    </div>
                `;
            } else {
                testSuggestions.style.display = 'none';
                addressResult.innerHTML = `
                    <div class="alert alert-warning">
                        <h6>⚠️ Подсказки не найдены</h6>
                        <p>${data.message || 'Нет подсказок для данного запроса'}</p>
                        <details>
                            <summary>Ответ сервера</summary>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </details>
                    </div>
                `;
            }
        } catch (error) {
            console.error('[v0] Test address error:', error);
            testSuggestions.style.display = 'none';
            addressResult.innerHTML = `
                <div class="alert alert-danger">
                    <h6>❌ Ошибка запроса</h6>
                    <p>${error.message}</p>
                </div>
            `;
        }
    }
    
    // Скрываем подсказки при клике вне поля
    document.addEventListener('click', function(e) {
        if (!testAddressInput.contains(e.target) && !testSuggestions.contains(e.target)) {
            testSuggestions.style.display = 'none';
        }
    });
});
</script>
@endsection
