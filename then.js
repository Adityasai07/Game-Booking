//2 thens
fetch('x.php')
  .then(res => {
    if (!res.ok) throw new Error("error");
    return res.json(); //  returns Promise
  })
  .then(data => {
    console.log(data); // now JSON is fully ready
  });



// nested then
fetch('y.php')
  .then(res => {
    if (!res.ok) throw new Error("error");
    res.json().then(data => {
      console.log(data); //can use data here
    });
  });

//with one then
fetch('z.php')
  .then(res => {
    if (!res.ok) throw new Error("error");
    data = res.json();
    console.log(data);//using wont work , promise .
    return res.json();
  });

