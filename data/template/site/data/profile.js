var student = window.student || {};

student.profile = {
  name: "YOUR NAME HERE",
  major: "YOUR MAJOR", // Name and Major are both 'strings', bake sure all your letters stay in the quotes
  location: 606, // Enter your zipcode.. should only be numbers
  // The courses property is an Array
  courses: [
    {
      title: "COURSE TITLE",
      time: 'YYYY-MM-DD HH:MM:SS' // for HH, use 24 hour clock, for SS, 00 will do
    },
    {
      title: 'ANOTHER COURSE',
      time: 'YYYY-MM-DD HH:MM:SS'
    } // if you put a comma after this bracket, you can add more courses using the syntax above.
      // the very last bracket should NOT have a comma after it.
  ]
};
