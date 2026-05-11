/* global axiosRequest */

function initAddressLocationCascade() {
    const citySelect =
        document.querySelector('[data-js="city-id"]') ||
        document.getElementById('city_id');
    const districtSelect =
        document.querySelector('[data-js="district-id"]') ||
        document.getElementById('district_id');
    const neighborhoodSelect =
        document.querySelector('[data-js="neighborhood-id"]') ||
        document.getElementById('neighborhood_id');

    if (!citySelect) {
        return;
    }

    const fillSelect = (select, items) => {
        if (!select) {
            return;
        }

        select.innerHTML = '<option hidden>Seçiniz</option>';
        items.forEach((item) => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            select.appendChild(option);
        });
    };

    const clearSelect = (select) => {
        if (!select) {
            return;
        }

        select.innerHTML = '<option hidden>Seçiniz</option>';
    };

    axiosRequest.get('/locations/cities', {}, {
        onSuccess: (response) => fillSelect(citySelect, response.data),
    });

    citySelect.addEventListener('change', () => {
        clearSelect(districtSelect);
        clearSelect(neighborhoodSelect);

        axiosRequest.get(`/locations/districts/${citySelect.value}`, {}, {
            onSuccess: (response) => fillSelect(districtSelect, response.data),
        });
    });

    if (!districtSelect) {
        return;
    }

    districtSelect.addEventListener('change', () => {
        clearSelect(neighborhoodSelect);

        axiosRequest.get(`/locations/neighborhoods/${districtSelect.value}`, {}, {
            onSuccess: (response) => fillSelect(neighborhoodSelect, response.data),
        });
    });
}

document.addEventListener('DOMContentLoaded', initAddressLocationCascade);

