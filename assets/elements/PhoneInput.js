import TomSelect from 'tom-select';
import Inputmask from 'inputmask';

export class PhoneInput extends HTMLElement {
    constructor() {
        super();
        this.countrySelect = null;
        this.phoneInput = null;
        this.mask = null;
        this.hiddenInput = null;
        this.countries = this.getCountriesData();
    }

    connectedCallback() {
        this.render();
        this.init();
    }

    disconnectedCallback() {
        if (this.countrySelect) {
            this.countrySelect.destroy();
        }
        if (this.mask) {
            this.mask.remove();
        }
    }

    render() {
        const initialCountry = this.getAttribute('data-initial-country') || 'fr';
        const onlyCountries = this.getAttribute('data-only-countries')?.split(',').map(c => c.trim());
        const countries = onlyCountries ? 
            this.countries.filter(c => onlyCountries.includes(c.code)) : 
            this.countries;

        const originalInput = this.querySelector('input[type="tel"]');
        const inputName = originalInput?.name || 'phone';
        const inputId = originalInput?.id || 'phone';
        const inputClass = originalInput?.className || 'form-input';
        const placeholder = originalInput?.placeholder || 'Numéro de téléphone';

        this.innerHTML = `
            <div class="phone-input-container">
                <div class="phone-input-row">
                    <div class="country-selector-wrapper">
                        <select id="${inputId}_country" class="country-select">
                            ${countries.map(country => `
                                <option value="${country.code}" 
                                        data-dial-code="${country.dialCode}"
                                        data-mask="${country.mask}"
                                        ${country.code === initialCountry ? 'selected' : ''}>
                                    ${country.flag} ${country.name} ${country.dialCode}
                                </option>
                            `).join('')}
                        </select>
                    </div>
                    <div class="phone-number-wrapper">
                        <input type="tel" 
                               id="${inputId}" 
                               name="${inputName}_display"
                               class="${inputClass} phone-number-input" 
                               placeholder="${placeholder}"
                               autocomplete="tel">
                        <input type="hidden" name="${inputName}" id="${inputId}_hidden">
                    </div>
                </div>
            </div>
        `;
    }

    init() {
        this.countrySelect = this.querySelector('.country-select');
        this.phoneInput = this.querySelector('.phone-number-input');
        this.hiddenInput = this.querySelector('input[type="hidden"]');

        this.initCountrySelect();
        this.initPhoneMask();
        this.attachEventListeners();
        
        // Initialiser avec le pays par défaut
        this.updatePhoneMask();
    }

    initCountrySelect() {
        this.tomSelect = new TomSelect(this.countrySelect, {
            valueField: 'value',
            labelField: 'text',
            searchField: ['text'],
            render: {
                option: (data, escape) => {
                    const option = this.countrySelect.querySelector(`option[value="${data.value}"]`);
                    const flag = option.textContent.split(' ')[0];
                    const name = option.textContent.split(' ').slice(1, -1).join(' ');
                    const dialCode = option.getAttribute('data-dial-code');
                    
                    return `
                        <div class="country-option">
                            <span class="country-flag">${flag}</span>
                            <span class="country-name">${escape(name)}</span>
                            <span class="country-dial-code">${escape(dialCode)}</span>
                        </div>
                    `;
                },
                item: (data, escape) => {
                    const option = this.countrySelect.querySelector(`option[value="${data.value}"]`);
                    const flag = option.textContent.split(' ')[0];
                    const dialCode = option.getAttribute('data-dial-code');
                    
                    return `
                        <div class="country-selected">
                            <span class="country-flag">${flag}</span>
                            <span class="country-dial-code">${escape(dialCode)}</span>
                        </div>
                    `;
                }
            },
            dropdownClass: 'country-dropdown',
            controlClass: 'country-control',
        });
    }

    initPhoneMask() {
        this.mask = new Inputmask({
            mask: '999 999 999',
            placeholder: ' ',
            clearMaskOnLostFocus: false,
            showMaskOnHover: false,
            showMaskOnFocus: false,
        });
    }

    attachEventListeners() {
        // Écouter les changements de pays
        this.tomSelect.on('change', () => {
            this.updatePhoneMask();
            this.updateHiddenValue();
        });

        // Écouter les changements du numéro
        this.phoneInput.addEventListener('input', () => {
            this.updateHiddenValue();
            this.validatePhone();
        });

        this.phoneInput.addEventListener('blur', () => {
            this.validatePhone();
        });
    }

    updatePhoneMask() {
        const selectedOption = this.countrySelect.options[this.countrySelect.selectedIndex];
        const mask = selectedOption.getAttribute('data-mask');
        const dialCode = selectedOption.getAttribute('data-dial-code');

        if (this.mask) {
            this.mask.remove();
        }

        // Appliquer le nouveau masque selon le pays
        this.mask = new Inputmask({
            mask: mask || '999 999 999',
            placeholder: ' ',
            clearMaskOnLostFocus: false,
            showMaskOnHover: false,
            showMaskOnFocus: true,
        });

        this.mask.mask(this.phoneInput);
        
        // Mettre à jour le placeholder avec un exemple
        const example = this.getPhoneExample(selectedOption.value);
        if (example) {
            this.phoneInput.placeholder = example;
        }
    }

    updateHiddenValue() {
        const selectedOption = this.countrySelect.options[this.countrySelect.selectedIndex];
        const dialCode = selectedOption.getAttribute('data-dial-code');
        const phoneNumber = this.phoneInput.value.replace(/\s/g, '');
        
        if (phoneNumber) {
            this.hiddenInput.value = dialCode + phoneNumber;
        } else {
            this.hiddenInput.value = '';
        }

        // Émettre un événement personnalisé
        this.dispatchEvent(new CustomEvent('phone-change', {
            detail: {
                country: selectedOption.value,
                dialCode: dialCode,
                phoneNumber: phoneNumber,
                fullNumber: this.hiddenInput.value,
                isValid: this.isValidPhone()
            }
        }));
    }

    validatePhone() {
        const isValid = this.isValidPhone();
        
        this.phoneInput.classList.remove('is-valid', 'is-invalid');
        
        if (this.phoneInput.value.trim()) {
            this.phoneInput.classList.add(isValid ? 'is-valid' : 'is-invalid');
        }

        return isValid;
    }

    isValidPhone() {
        const phoneNumber = this.phoneInput.value.replace(/\s/g, '');
        const selectedCountry = this.countrySelect.value;
        
        // Validation basique selon le pays
        const validations = {
            'fr': /^\d{9}$/, // 9 chiffres pour la France
            'gb': /^\d{10,11}$/, // 10-11 chiffres pour UK
            'us': /^\d{10}$/, // 10 chiffres pour US
            'de': /^\d{10,11}$/, // 10-11 chiffres pour Allemagne
            'es': /^\d{9}$/, // 9 chiffres pour Espagne
            'it': /^\d{9,10}$/, // 9-10 chiffres pour Italie
        };

        const pattern = validations[selectedCountry] || /^\d{7,15}$/;
        return pattern.test(phoneNumber);
    }

    getPhoneExample(countryCode) {
        const examples = {
            'fr': '01 23 45 67 89',
            'gb': '020 7946 0958',
            'us': '(555) 123-4567',
            'de': '030 12345678',
            'es': '912 34 56 78',
            'it': '06 1234 5678',
        };
        return examples[countryCode] || '';
    }

    getCountriesData() {
        return [
            { code: 'fr', name: 'France', dialCode: '+33', flag: '🇫🇷', mask: '99 99 99 99 99' },
            { code: 'gb', name: 'United Kingdom', dialCode: '+44', flag: '🇬🇧', mask: '99 9999 9999' },
            { code: 'us', name: 'United States', dialCode: '+1', flag: '🇺🇸', mask: '(999) 999-9999' },
            { code: 'de', name: 'Germany', dialCode: '+49', flag: '🇩🇪', mask: '999 999 9999' },
            { code: 'es', name: 'Spain', dialCode: '+34', flag: '🇪🇸', mask: '999 99 99 99' },
            { code: 'it', name: 'Italy', dialCode: '+39', flag: '🇮🇹', mask: '999 999 9999' },
            { code: 'be', name: 'Belgium', dialCode: '+32', flag: '🇧🇪', mask: '999 99 99 99' },
            { code: 'ch', name: 'Switzerland', dialCode: '+41', flag: '🇨🇭', mask: '99 999 99 99' },
            { code: 'nl', name: 'Netherlands', dialCode: '+31', flag: '🇳🇱', mask: '99 999 9999' },
            { code: 'ca', name: 'Canada', dialCode: '+1', flag: '🇨🇦', mask: '(999) 999-9999' },
            { code: 'au', name: 'Australia', dialCode: '+61', flag: '🇦🇺', mask: '999 999 999' },
        ];
    }

    // API publique
    getValue() {
        return this.hiddenInput.value;
    }

    setValue(value) {
        // Détecter le pays depuis le numéro complet
        if (value.startsWith('+')) {
            const dialCode = value.substring(0, 3);
            const country = this.countries.find(c => c.dialCode === dialCode);
            if (country) {
                this.tomSelect.setValue(country.code);
                this.phoneInput.value = value.substring(3);
                this.updateHiddenValue();
            }
        }
    }

    isValid() {
        return this.isValidPhone();
    }
}