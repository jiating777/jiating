import {ChangeDetectorRef, Component, OnInit} from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';
import {UserServiceService} from '../../services/user-service.service';
import {MessageService} from '../../services/message.service';
import {NgForm} from '@angular/forms';

@Component({
  selector: 'app-edit-shop1',
  templateUrl: './edit-shop1.page.html',
  styleUrls: ['./edit-shop1.page.scss'],
})
export class EditShop1Page implements OnInit {
  title: string;
  property: string;
  value: string;

  constructor(private activatedRoute: ActivatedRoute,
              private userService: UserServiceService,
              private router: Router,
              private messageServer: MessageService,
              private cd: ChangeDetectorRef) {
    this.title = activatedRoute.snapshot.params.title;
    this.property = activatedRoute.snapshot.params.property;
    console.log('修改' + this.title);
  }

  ngOnInit() {
  }
  onSave(form: NgForm) {
    console.log(form.value);
    this.userService.modify(this.property, form.value.value);
    this.messageServer.alertMessage('提示', '修改成功', 1);
    this.cd.detectChanges();
    this.router.navigateByUrl('/shop');
  }

}
