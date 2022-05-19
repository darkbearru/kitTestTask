export async function myFetch (url = '', method = 'POST', data = {}) 
{
    const response = await fetch(url, {
        method: method, 
        mode: 'cors', 
        cache: 'no-cache', 
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json'
        },
        redirect: 'follow',
        referrerPolicy: 'no-referrer', 
        body: JSON.stringify (data)
    });
    return response;
}

export function makeQueryParams (data)
{
    let result = ''
    for (let i in data) {
        if (!data.hasOwnProperty (i)) continue;
        result += (result ? '&' : '') + `${i}=${data[i]}`;
    }
    return (result ? `?${result}` : '');
}
