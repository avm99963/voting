chrome.certificateProvider.onCertificatesRequested.addListener(function(reportCallback) {
  var certs = [];
  var certInfo = {
    certificate: new Uint8Array(),
    supportedHashes: ['SHA256']
  };
  certs.push(certInfo);
  
  reportCallback(certs, function(rejectedCerts) {
    if (chrome.runtime.lastError) {
      return;
    }
    
    if (rejectedCerts.length !== 0) {
      onCertificatesRejected(rejectedCerts);
    }
  });
});