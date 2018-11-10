import { Component, OnInit } from '@angular/core';
import {UserServiceService} from '../../services/user-service.service';
import {MessageService} from '../../services/message.service';
import {Router} from '@angular/router';
import {LocalStorageService} from '../../services/local-storage.service';

@Component({
  selector: 'app-change-password',
  templateUrl: './change-password.page.html',
  styleUrls: ['./change-password.page.scss'],
})
export class ChangePasswordPage implements OnInit {

  change = {
    password: '',
    confirmPassword: '',
  }

  constructor(private userService: UserServiceService,
              private messageServer: MessageService,
              private router: Router,
              private localStorage: LocalStorageService) { }

  ngOnInit() {
  }
  changePawd() {
    let username = this.localStorage.get('currentUser', 'null');
    this.userService.changePassword(this.change.password, username);
    this.messageServer.toastMessage('修改成功', '2');
    this.router.navigateByUrl('\setting');
  }

}
