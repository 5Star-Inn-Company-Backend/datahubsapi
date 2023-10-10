<div class="pre">
    <table border="0" width="680" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
            <td width="680" height="25">
                <div style="margin-right: 5px; margin-left: 150px" align="left"><img src="https://5starcompany.com.ng/images/mcd_logo.png" width="100px" height="100px" /></div>
            </td>
        </tr>
        <tr>
            <td>
                <table border="0" width="680" cellspacing="0" cellpadding="0">
                    <tbody>
                    <tr>
                        <td width="15">&nbsp;</td>
                        <td width="650">
                            <table border="0" width="100%" cellspacing="0" cellpadding="0">
                                <tbody>
                                <tr>
                                    <td>
                                        <p>&nbsp;</p>
                                        <p>Someone recently requested to reset your account password for &ldquo;{{$data['user_name']}}&rdquo;.</p>
                                        <p>Your new password is:</p>
                                        <p><strong>{{$data['password']}}</strong></p>
                                        <br/>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div>
                                            <p>Device Details: {{$data['device']}}</p>
                                            <p>IP address: {{$data['ip']}}</p>
                                            <p>Date &
                                                Time: {{\Carbon\Carbon::now()->format('D, M d, Y  H:i:s a T')}}</p>
                                        </div>
                                        <p>
                                            If this wasn't you, your account has been compromised. Please follow these
                                            steps: <br/>
                                            1. Reset your password.<br/>
                                            2. Review your security info.<br/>
                                            3. Learn how to make your account more secure.
                                        </p>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                        <td width="15">&nbsp;</td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td align="center">
                <br />
                This mail was sent with ❤ from Mega Cheap Data to {{$data['email']}}
                <br />
                <p>Copyright&copy;&nbsp;2020 Mega Cheap Data, 5Star Inn Company</p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
